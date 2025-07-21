<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OwnerCarStoreUpdateRequest extends FormRequest
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
            'car_type_id' => 'required|exists:m_car_types,id',
            // 'owner_id' => 'required|exists:owners,id',
            'plate_number' => 'required|string|unique:owner_cars,plate_number,' . ($this->owner_car ?? '')
        ];
    }
}
