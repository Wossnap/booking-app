<?php

namespace Tests\Feature;

use App\Models\EventClient;
use App\Models\Service;
use App\Models\WorkingHour;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Traits\UsesTestDataSeeder;
use Carbon\Carbon;

class BookableScheduleTest extends TestCase
{
    use RefreshDatabase, UsesTestDataSeeder;

    public function test_spa_gets_all_required_data_to_display_a_calendar_and_time_selection(): void
    {
        $this->withoutExceptionHandling();

        $this->addMenHaircutDataForTest();

        $response = $this->get('/api/bookable-schedules');

        $response->assertStatus(200);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data', 1, fn($json) => $json
                ->has('id')
                ->has('name')
                ->has('bookable_schedule.0', fn($json) => $json
                    ->has('date')
                    ->has('time_slots.0', fn($json) => $json
                        ->has('start_time')
                        ->has('end_time')
                        ->has('clients_that_can_book')
                    )
                )
            )
            ->has('links')
            ->has('meta')
        );
    }

    public function test_time_slots_for_men_hair_cut_is_correct(): void
    {
        $this->withoutExceptionHandling();

        $this->addMenHaircutDataForTest();

        $event = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail()->events()->create([
            'date' => now()->addDay(),
            'start_time' => '08:00',
            'end_time' => '08:05',
        ]);

        $event2 = Service::where('name', Service::MEN_HAIRCUT)->firstOrFail()->events()->create([
            'date' => now()->addDay(),
            'start_time' => '08:10',
            'end_time' => '08:15',
        ]);

        EventClient::factory()->for($event)->create();
        EventClient::factory(2)->for($event2)->create();
        // $event->clients()->create([
        //     'first_name' => fake()->firstName(),
        //     'last_name' => fake()->lastName(),
        //     'email' => fake()->unique()->safeEmail(),
        // ]);
        // $event2->clients()->create([
        //     'first_name' => fake()->firstName(),
        //     'last_name' => fake()->lastName(),
        //     'email' => fake()->unique()->safeEmail(),
        // ]);
        // $event2->clients()->create([
        //     'first_name' => fake()->firstName(),
        //     'last_name' => fake()->lastName(),
        //     'email' => fake()->unique()->safeEmail(),
        // ]);

        $response = $this->get('/api/bookable-schedules');

        $response->assertStatus(200);

        // dump($response->json());

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data.0', fn($json) => $json
                ->has('id')
                ->has('name')
                ->has('bookable_schedule', 6)
                ->has('bookable_schedule.1', fn($json) => $json
                    ->where('date',now()->addDays(1)->format('Y-m-d'))
                    ->has('time_slots',60)
                    ->has('time_slots.0', fn($json) => $json//testing to see if there is slots every 10 minutes
                        ->where('start_time', '08:00')
                        ->where('end_time', '08:05')
                        ->where('clients_that_can_book', 2)
                    )
                    ->has('time_slots.1', fn($json) => $json
                        ->where('start_time', '08:10')
                        ->where('end_time', '08:15')
                        ->where('clients_that_can_book', 1)
                    )
                )
            )
            ->has('links')
            ->has('meta')
        );

        $response->assertDontSee(['date' => now()->addDays(3)->format('Y-m-d')]);
        $response->assertDontSee(['date' => now()->next(WorkingHour::SUNDAY)->format('Y-m-d')]);

    }

    public function test_time_slots_for_women_hair_cut_is_correct(): void
    {
        $this->withoutExceptionHandling();

        $this->addWomenHairCutDataForTest();

        $response = $this->get('/api/bookable-schedules');

        $response->assertStatus(200);

        // dump($response->json());

        $response->assertJson(fn (AssertableJson $json) => $json
            ->has('data.0', fn($json) => $json
                ->has('id')
                ->has('name')
                ->has('bookable_schedule', 6)
                ->has('bookable_schedule.1', fn($json) => $json
                    ->where('date',now()->addDays(1)->format('Y-m-d'))
                    ->has('time_slots',10)
                    ->has('time_slots.0', fn($json) => $json//testing to see if there is slots every 1 hour
                        ->where('start_time', '08:00')
                        ->where('end_time', '08:50')
                        ->where('clients_that_can_book', 3)
                    )
                    ->has('time_slots.1', fn($json) => $json
                        ->where('start_time', '09:00')
                        ->where('end_time', '09:50')
                        ->where('clients_that_can_book', 3)
                    )
                )
            )
            ->has('links')
            ->has('meta')
        );

        $response->assertDontSee(['date' => now()->addDays(3)->format('Y-m-d')]);
        $response->assertDontSee(['date' => now()->next(WorkingHour::SUNDAY)->format('Y-m-d')]);

    }


}
