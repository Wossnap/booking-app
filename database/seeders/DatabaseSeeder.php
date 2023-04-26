<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\OffDate;
use App\Models\OffTime;
use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Database\Seeder;
use App\Traits\UsesTestDataSeeder;

class DatabaseSeeder extends Seeder
{
    use UsesTestDataSeeder;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
       $this->addMenHaircutDataForTest();
       $this->addWomenHaircutDataForTest();
    }
}
