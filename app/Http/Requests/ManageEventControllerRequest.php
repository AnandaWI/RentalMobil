<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageEventControllerRequest extends FormRequest
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
            'subject' => ['required', 'string'],
            'content' => ['required', 'string'],
            'link' => ['required', 'string'],
            'publish_date' => ['required', 'date'],
            'is_published' => ['nullable', 'boolean']
        ];
    }
}
