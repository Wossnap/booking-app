<?php

namespace Tests\Feature;

use App\Models\EventClient;
use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Traits\UsesTestDataSeeder;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Validation\ValidationException;

class BookingTest extends TestCase
{
    use UsesTestDataSeeder, RefreshDatabase;

    public function test_a_schedule_can_be_booked(): void
    {
        $this->withoutExceptionHandling();

        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(201);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', fn ($json) => $json
                ->has('id')
                ->has('service_id')
                ->where('date', now()->addDay()->format('Y-m-d'))
                ->where('start_time', '08:00')
                ->where('end_time', '08:05')
                ->has('clients',1)
                ->has('created_at')
                ->has('updated_at')
            )
        );
    }

    public function test_a_schedule_can_be_booked2(): void
    {
        $this->withoutExceptionHandling();

        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(201);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', fn ($json) => $json
                ->has('id')
                ->has('service_id')
                ->where('date', now()->addDay()->format('Y-m-d'))
                ->where('start_time', '08:00')
                ->where('end_time', '08:05')
                ->has('clients',2)
                ->has('created_at')
                ->has('updated_at')
            )
        );
    }

    public function test_a_schedule_can_be_booked3(): void
    {
        $this->withoutExceptionHandling();

        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(201);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', fn ($json) => $json
                ->has('id')
                ->has('service_id')
                ->where('date', now()->addDay()->format('Y-m-d'))
                ->where('start_time', '08:00')
                ->where('end_time', '08:05')
                ->has('clients',3)
                ->has('created_at')
                ->has('updated_at')
            )
        );
    }

    public function test_a_schedule_cant_be_booked_if_the_date_is_in_the_past(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->subDay()->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['date']);
    }

    public function test_a_schedule_cant_be_booked_if_the_date_is_after_the_bookable_days(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDays(20)->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['date']);
    }

    public function test_a_schedule_cant_be_booked_if_the_date_is_in_planned_off_days(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDays(3)->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['date']);
    }

    public function test_a_schedule_cant_be_booked_if_a_slot_is_booked_out(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $event = $service->events()->create([
            'date' => now()->addDay(),
            'start_time' => '08:00',
            'end_time' => '08:05',
        ]);

        EventClient::factory(3)->for($event)->create();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    public function test_a_schedule_cant_be_booked_if_a_slot_cant_satisfy_number_of_clients(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $event = $service->events()->create([
            'date' => now()->addDay(),
            'start_time' => '08:00',
            'end_time' => '08:05',
        ]);

        EventClient::factory(2)->for($event)->create();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['clients']);
    }

    public function test_a_schedule_cant_be_booked_if_a_slot_is_before_working_hour(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '07:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    public function test_a_schedule_cant_be_booked_if_a_slot_is_after_working_hour(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '23:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    public function test_a_schedule_cant_be_booked_if_a_slot_is_with_in_a_configured_off_time(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '12:00',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    public function test_a_schedule_cant_be_booked_if_it_doesnt_fit_any_slot(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '10:02',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    public function test_a_schedule_cant_be_booked_if_it_is_within_break_between_events(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '10:06',
            'clients' => [
                [
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'email' => fake()->email(),
                ],
            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['time']);
    }

    public function test_a_schedule_cant_be_booked_if_client_detail_is_missing(): void
    {
        $this->addMenHaircutDataForTest();

        $service = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail();

        $response = $this->post('/api/bookings/services/' . $service->id, [
            'date' => now()->addDays(20)->format('Y-m-d'),
            'time' => '08:00',
            'clients' => [

            ]
        ]);

        $response->assertStatus(422);
        $response->assertInvalid(['clients']);
    }
}
