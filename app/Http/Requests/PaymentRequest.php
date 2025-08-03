<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'address' => 'required|string',
            'phone_number' => 'required|string',
            'email' => 'required|email',
            'destination_id' => 'required|exists:m_destinations,id',
            'day' => 'required|integer',
            'rent_date' => 'required|date',
            'pickup_time' => 'required|date_format:H:i',
            // 'pick_up_location' => 'required|string',
            'total_price' => 'required|integer',
            'order_details' => 'required|array',
            'order_details.*.driver_id' => 'nullable|exists:m_drivers,id',
            'order_details.*.owner_car_type_id' => 'required|exists:m_car_types,id',
            'order_details.*.count' => 'required|integer',
            'detail_destination' => 'required|string'
        ];
    }
}
