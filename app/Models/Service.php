<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Ramsey\Uuid\Type\Integer;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'breaks',
        'bookable_days',
        'number_of_clients'
    ];

    public function workingHours(): Relation{
        return $this->hasMany(WorkingHour::class, 'service_id');
    }

    public function offTimes(): Relation
    {
        return $this->hasMany(OffTime::class, 'service_id');
    }

    public function offDates(): Relation
    {
        return $this->hasMany(OffDate::class, 'service_id');
    }

    public function events(): Relation
    {
        return $this->hasMany(Event::class, 'service_id');
    }

    public function getClientsAvailableFor(Carbon $date, Carbon $startTime, Carbon $endTime): int
    {
        $clientsAvailableFor = $this->number_of_clients;

        if($event = $this->events()->where('date', $date->format('Y-m-d'))->where('start_time', $startTime->format('H:i:s'))->where('end_time', $endTime->format('H:i:s'))->first()){
            $bookedEvents = $event->clients()->count();

            $clientsAvailableFor -= $bookedEvents;
        }

        return $clientsAvailableFor;
    }

    public function getBookableScheduleAttribute() : array
    {
        $workingHours = $this->workingHours;

        $startDate = now();
        $endDate = now()->addDays($this->bookable_days);

        $bookableSchedule = array();

        for($date = $startDate; $date <= $endDate; $date->addDay()){
            $pass = false;
            $timeSlots = array();

            //skip the date if it is found in offDates

            $isInOffDate = false;

            foreach($this->offDates as $offDate){
                if($date->isSameDay(Carbon::parse($offDate->start_date))){
                    $isInOffDate = true;
                }
            }

            if($isInOffDate){
                continue;
            }

            $isWorkingHourSetForTheDate = false;
            //iterate and check if there is a working hour set for the date in the current index in the loop
            //and add time slots
            foreach($workingHours as $workingHour){
                if($date->format('l') == $workingHour->day){
                    $isWorkingHourSetForTheDate = true;

                    $startTime = $workingHour->start_time;
                    $startTimeArray = explode(':',$startTime);

                    $startTime = Carbon::parse($date)->setTime($startTimeArray[0],$startTimeArray[1]);

                    $endTime = $workingHour->end_time;
                    $endTimeArray = explode(':',$endTime);

                    $endTime = Carbon::parse($date)->setTime($endTimeArray[0],$endTimeArray[1]);

                    //TODO: think about when incrementing and the time slots end time is greter than the endTime which shouldn't work
                    //TODO: need to handle that timeslots shouldn't be created for the day if it is pas the current time
                    for($time = Carbon::parse($startTime); $time < $endTime; $time->addMinutes($this->duration + $this->breaks)){
                        //check if the time passed and remove the slot if it has passed
                        if($time < now()->addHours(3)){
                            continue;
                        }

                        //check if it is not in off times
                        $isInOffTime = false;
                        $slotStartTime = Carbon::parse($time);
                        $slotEndTime = Carbon::parse($time)->addMinute($this->duration);

                        foreach($this->offTimes as $offTime){
                            $offTimeStartTime = $offTime->start_time;
                            $offStartTimeArray = explode(':',$offTimeStartTime);

                            $offTimeStartTime = Carbon::parse($date)->setTime($offStartTimeArray[0],$offStartTimeArray[1]);
                            // dump($startTime->toString());

                            $offTimeEndTime = $offTime->end_time;
                            $offEndTimeArray = explode(':',$offTimeEndTime);

                            $offTimeEndTime = Carbon::parse($date)->setTime($offEndTimeArray[0],$offEndTimeArray[1]);

                            // dump($slotStartTime->toString());
                            // dump($slotEndTime->toString());
                            // dd($offTimeStartTime > $slotStartTime);
                            if($offTimeStartTime <= $slotStartTime && $offTimeEndTime > $slotStartTime){
                                $isInOffTime = true;
                            }

                            if($offTimeStartTime < $slotEndTime && $offTimeEndTime > $slotEndTime){
                                $isInOffTime = true;
                            }
                        }
                        if($isInOffTime){
                            continue;
                        }

                        //calculate availability for clients
                        $clientsAvailableFor = $this->getClientsAvailableFor($date, $slotStartTime, $slotEndTime);

                        if($clientsAvailableFor <= 0){
                            continue;
                        }

                        $timeSlots[] = [
                            'start_time' => $slotStartTime->format('H:i'),
                            'end_time' => $slotEndTime->format('H:i'),
                            'clients_available_for' => $clientsAvailableFor
                        ];
                    }
                }
            }

            if(! $isWorkingHourSetForTheDate){
                continue;
            }



// TODO: for each day add time slot by adding the duration to the date using addMinutes and adding it to an array with the number of clients it is available for by checking the appointment table for any appointments in that specific time slot.
// dd();
// dd($date->setTime());
$bookableSchedule[]= [
    'date' => $date->format('Y-m-d'),
    'time_slots' => $timeSlots,
    // client amounts should be added
];
        }

        return $bookableSchedule;
    }


    const MEN_HAIRCUT = 'Men Haircut';
    const WOMEN_HAIRCUT = 'Woman Haircut';
}
