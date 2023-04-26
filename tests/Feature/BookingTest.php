<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\UsesTestDataSeeder;

class BookingTest extends TestCase
{
    use UsesTestDataSeeder;
    /**
     * A basic feature test example.
     */
    public function test_a_schedule_can_be_booked(): void
    {
        $this->withoutExceptionHandling();

        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay(1)->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(200);
    }
}
