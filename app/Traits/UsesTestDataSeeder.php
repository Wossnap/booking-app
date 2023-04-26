<?php

namespace App\Traits;

use App\Models\OffDate;
use App\Models\OffTime;
use App\Models\Service;
use App\Models\WorkingHour;

trait UsesTestDataSeeder
{
    public function addMenHaircutDataForTest(): void{
        if(! Service::where('name', Service::MEN_HAIRCUT)->exists()){
            $service = Service::create([
                'name' => Service::MEN_HAIRCUT,
                'duration' => 5,
                'breaks' => 5,
                'bookable_days' => 7,
                'number_of_clients' => 3
            ]);

            $workingHours = [
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::MONDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::TUESDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::WEDNESDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::THURSDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::FRIDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::SATURDAY,
                    'start_time' => '10:00',
                    'end_time' => '22:00'
                ],
            ];

            WorkingHour::insert($workingHours);


            $offTimes = [
                [
                    'service_id' => $service->id,
                    'name' => 'Lunch Break',
                    'start_time' => '12:00',
                    'end_time' => '13:00'
                ],
                [
                    'service_id' => $service->id,
                    'name' => 'Cleaning Break',
                    'start_time' => '15:00',
                    'end_time' => '16:00'
                ],
            ];

            OffTime::insert($offTimes);

            $offDates = [
                [
                    'service_id' => $service->id,
                    'name' => 'Public Holiday',
                    'start_date' => now()->addDays(3),
                ],
            ];

            OffDate::insert($offDates);
        }
    }

    public function addWomenHairCutDataForTest(): void{
        if(! Service::where('name', Service::WOMEN_HAIRCUT)->exists()){
            $service = Service::create([
                'name' => Service::WOMEN_HAIRCUT,
                'duration' => 50,
                'breaks' => 10,
                'bookable_days' => 7,
                'number_of_clients' => 3
            ]);

            $workingHours = [
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::MONDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::TUESDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::WEDNESDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::THURSDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::FRIDAY,
                    'start_time' => '08:00',
                    'end_time' => '20:00'
                ],
                [
                    'service_id' => $service->id,
                    'day' => WorkingHour::SATURDAY,
                    'start_time' => '10:00',
                    'end_time' => '22:00'
                ],
            ];

            WorkingHour::insert($workingHours);


            $offTimes = [
                [
                    'service_id' => $service->id,
                    'name' => 'Lunch Break',
                    'start_time' => '12:00',
                    'end_time' => '13:00'
                ],
                [
                    'service_id' => $service->id,
                    'name' => 'Cleaning Break',
                    'start_time' => '15:00',
                    'end_time' => '16:00'
                ],
            ];

            OffTime::insert($offTimes);

            $offDates = [
                [
                    'service_id' => $service->id,
                    'name' => 'Public Holiday',
                    'start_date' => now()->addDays(3),
                ],
            ];

            OffDate::insert($offDates);
        }
    }

}
