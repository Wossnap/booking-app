<?php

namespace App\Models;

use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

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

    public function workingHours(): Relation
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function offTimes(): Relation
    {
        return $this->hasMany(OffTime::class);
    }

    public function offDates(): Relation
    {
        return $this->hasMany(OffDate::class);
    }

    public function events(): Relation
    {
        return $this->hasMany(Event::class);
    }

    public function getBookableScheduleAttribute(): array
    {
        return (new BookingService())->getBookableSchedule($this);
    }

    //calculates the number of clients that can book
    public function getClientsThatCanBook(Carbon $date, Carbon $startTime): int
    {
        $clientsThatCanBook = $this->number_of_clients;

        if ($event = $this->events()->where('date', $date->format('Y-m-d'))->where('start_time', $startTime->format('H:i:s'))->where('end_time', Carbon::parse($startTime)->addMinute($this->duration)->format('H:i:s'))->first()) {
            $bookedEvents = $event->clients()->count();

            $clientsThatCanBook -= $bookedEvents;
        }

        return $clientsThatCanBook;
    }

    const MEN_HAIRCUT = 'Men Haircut';
    const WOMEN_HAIRCUT = 'Woman Haircut';
}
