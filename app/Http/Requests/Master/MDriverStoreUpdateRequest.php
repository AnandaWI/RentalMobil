<?php

namespace App\Http\Requests\Master;

use Illuminate\Foundation\Http\FormRequest;

class MDriverStoreUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'pengalaman' => 'required|integer|min:0',
            'tgl_lahir' => 'required|date',
            'img_url' => 'required|string|max:255'
        ];
    }
}
