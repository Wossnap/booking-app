<?php

namespace Tests;

use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

       /**
     * Setting up Accept: 'application/json'
     * because the test case is testing api
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withHeaders([
            'Accept' => 'application/json',
        ]);

        Carbon::setTestNow(now()->next(WorkingHour::WEDNESDAY)->setTimeFromTimeString('10:00'));
    }
}
