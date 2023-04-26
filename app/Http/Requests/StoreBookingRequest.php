<?php

namespace App\Http\Requests;

use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'clients' => 'required|array|max:3|min:1',
            'clients.*' => 'required|array:first_name,last_name,email',
            'clients.*.first_name' => 'required|string|regex:/^\S*$/u|max:50',
            'clients.*.last_name' => 'required|string|regex:/^\S*$/u|max:50',
            'clients.*.email' => 'required|email'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->date && $this->time && $this->clients) {
                if ((new BookingService())->isDateAndTimeNotBookable($this->service, Carbon::parse($this->date), Carbon::parse($this->time))) {
                    $validator->errors()->add('date', 'The specified date & time is not within the bookable schedule!');
                }

                if ((new BookingService())->isDateInOffDates($this->service, Carbon::parse($this->date))) {
                    $validator->errors()->add('date', 'The specified day is within our off days!');
                }

                if ((new BookingService())->isSlotBookedOut($this->service, Carbon::parse($this->date), Carbon::parse($this->time))) {
                    $validator->errors()->add('time', 'The specified slot is booked out!');
                }
                if ((new BookingService())->isSlotNotAvailableForClients($this->service, Carbon::parse($this->date), Carbon::parse($this->time), count($this->clients))) {
                    $validator->errors()->add('clients', 'The specified slot can not satisfy the specified amount of clients!');
                }
                if ((new BookingService())->isTimeInOffTimes($this->service, Carbon::parse($this->time))) {
                    $validator->errors()->add('time', 'The specified slot is within a configured off time!');
                }
                if ((new BookingService())->isSlotNotWithinWorkingHour($this->service, Carbon::parse($this->date), Carbon::parse($this->time))) {
                    $validator->errors()->add('time', 'The specified slot is not within working hours!');
                }
                if ((new BookingService())->isSlotNotFittingBookableSlots($this->service, Carbon::parse($this->date), Carbon::parse($this->time))) {
                    $validator->errors()->add('time', 'The specified slot does not fit in bookable slots!');
                }


            }
        });
    }
}
