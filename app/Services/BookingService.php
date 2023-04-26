<?php

namespace App\Services;

use App\Models\Service;
use Carbon\Carbon;

class BookingService {
    public function isDateInOffDates(Service $service, Carbon $date): bool{
        foreach($service->offDates as $offDate){
            if($date->isSameDay(Carbon::parse($offDate->start_date))){
                return true;
            }
        }

        return false;
    }

    public function isDateAndTimeNotBookable(Service $service, Carbon $date, Carbon $time)
    {
        if(Carbon::parse($date)->setTimeFromTimeString($time->format('H:i'))->lessThan(now())){
            return true;
        }

        if(Carbon::parse($date)->greaterThan(now()->addDays($service->bookable_days))){
            return true;
        }

        if(! $service->workingHours()->where('day',$date->format('l'))->exists()){
            return true;
        }

        return false;
    }

    public function isTimeInOffTimes(Service $service, Carbon $time): bool{
        $slotStartTime = Carbon::parse($time);
        $slotEndTime = Carbon::parse($time)->addMinute($service->duration);

        foreach($service->offTimes as $offTime){
            $offTimeStartTime = Carbon::parse($time)->setTimeFromTimeString($offTime->start_time);
            $offTimeEndTime = Carbon::parse($time)->setTimeFromTimeString($offTime->end_time);

            if($offTimeStartTime <= $slotStartTime && $offTimeEndTime > $slotStartTime){
                return true;
            }

            if($offTimeStartTime < $slotEndTime && $offTimeEndTime > $slotEndTime){
                return true;
            }
        }

        return false;
    }

    // checks if the slot start time is before the current time and if the slot end time is after the working hour end time
    public function isSlotNotWithinWorkingHour(Service $service, Carbon $date, Carbon $time)
    {
        if($workingHour = $service->workingHours()->where('day',$date->format('l'))->first()){

            $slotStartTime = Carbon::parse($date)->setTimeFromTimeString($time->format('H:i'));
            $workHourStartTime = Carbon::parse($date)->setTimeFromTimeString($workingHour->start_time);

            if($slotStartTime->lessThan($workHourStartTime)){
                return true;
            }

            $slotEndTime = Carbon::parse($date)->setTimeFromTimeString($time->format('H:i'))->addMinute($service->duration);
            $workHourEndTime = Carbon::parse($date)->setTimeFromTimeString($workingHour->end_time);

            if($slotEndTime->greaterThan($workHourEndTime)){
                return true;
            }

        }

        return false;
    }

    public function isSlotBookedOut(Service $service, Carbon $date, Carbon $time)
    {
        if($service->getClientsThatCanBook($date, $time) <= 0){
            return true;
        }

        return false;
    }

    public function isSlotNotAvailableForClients(Service $service, Carbon $date, Carbon $time, int $numberOfClients)
    {
        if($service->getClientsThatCanBook($date, $time) < $numberOfClients){
            return true;
        }

        return false;
    }

    public function isSlotNotFittingBookableSlots(Service $service, Carbon $date, Carbon $time)
    {
        if($timeSlots = $this->getBookableScheduleForADay($service, $date)){
            $timeSlots = collect($timeSlots);
            return (! collect($timeSlots)->contains(fn (array $value, int $key) =>
                $value['start_time'] == $time->format('H:i')
            ));
        }else{
            return true;
        }
    }

    public function getBookableScheduleForADay(Service $service, Carbon $date){
        if($workingHour = $service->workingHours()->where('day',$date->format('l'))->first()){
            $timeSlots = array();

            $workHourStartTime = Carbon::parse($date)->setTimeFromTimeString($workingHour->start_time);
            $workHourEndTime = Carbon::parse($date)->setTimeFromTimeString($workingHour->end_time);

            for($time = Carbon::parse($workHourStartTime); $time < $workHourEndTime; $time->addMinutes($service->duration + $service->breaks)){

                $slotStartTime = Carbon::parse($time);

                if($this->isDateAndTimeNotBookable($service, $date, $slotStartTime)){
                    continue;
                }

                if($this->isSlotNotWithinWorkingHour($service, $date, $slotStartTime)){
                    continue;
                }

                if($this->isTimeInOffTimes($service, $slotStartTime)){
                    continue;
                }

                if($this->isSlotBookedOut($service, $date, $slotStartTime)){
                    continue;
                }

                $timeSlots[] = [
                    'start_time' => $slotStartTime->format('H:i'),
                    'end_time' => Carbon::parse($slotStartTime)->addMinute($service->duration)->format('H:i'),
                    'clients_that_can_book' => $service->getClientsThatCanBook($date, $slotStartTime)
                ];
            }

            return $timeSlots;

        }else{
            return null;
        }
    }

    public function getBookableSchedule(Service $service): array
    {
        $startDate = now();
        $endDate = now()->addDays($service->bookable_days);

        $bookableSchedule = array();

        for($date = $startDate; $date <= $endDate; $date->addDay()){
            if($this->isDateInOffDates($service, $date)){
               continue;
            }

            if($timeSlots =$this->getBookableScheduleForADay($service, $date)){
                $bookableSchedule[]= [
                    'date' => $date->format('Y-m-d'),
                    'time_slots' => $timeSlots,
                ];
            }else{
                continue;
            }
        }
        return $bookableSchedule;
    }
}
