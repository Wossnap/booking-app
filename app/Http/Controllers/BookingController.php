<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Models\Service;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request, Service $service)
    {
        $event = $service->events()->firstOrCreate([
            'date' => $request->date,
            'start_time' => $request->time
        ],[
            'end_time' => Carbon::parse($request->time)->addMinutes($service->duration)->format('H:i')
        ]);

        foreach($request->safe()->clients as $client){
            $event->clients()->create($client);
        }

        return EventResource::make($event->load('clients'));
    }
}
