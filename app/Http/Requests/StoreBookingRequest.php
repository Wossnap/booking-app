<?php

namespace App\Http\Requests;

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
            'clients' => 'required|array|max:3',
            'clients.*.first_name' => 'required|string|regex:/^\S*$/u|alpha',
            'clients.*.last_name' => 'required|string|regex:/^\S*$/u|alpha',
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
            dd($this->date);
            $validator->errors()->add('products', 'At least one product or package must be selected!');
        });
    }
}
