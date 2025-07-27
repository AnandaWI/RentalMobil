<?php

namespace App\Http\Requests;

use App\Models\MCarType;
use Illuminate\Foundation\Http\FormRequest;

class ManageDestinationRequest extends FormRequest
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
        $carTypeIds = MCarType::pluck('id')->toArray(); // Ambil semua ID car type
        $rules = [
            'name' => ['required', 'string'],
            'posibility_day' => ['required', 'numeric'],
            'car_destination_price' => ['required', 'array'],
            'car_destination_price.*.car_type_id' => ['required', 'in:' . implode(',', $carTypeIds)],
            'car_destination_price.*.price' => ['required', 'numeric'],
        ];

        return $rules;
    }
}
