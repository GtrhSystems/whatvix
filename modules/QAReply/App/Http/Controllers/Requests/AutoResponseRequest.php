<?php

namespace Modules\QAReply\App\Http\Controllers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoResponseRequest extends FormRequest
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
            'title' => ['required', 'min:2', 'max:100'],
            'items.*.key' => ['required', 'min:2', 'max:100'],
            'items.*.value' => ['required', 'min:2', 'max:500'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.min' => 'The title field must be at least 2 characters.',
            'title.max' => 'The title field may not be greater than 100 characters.',
            'items.*.key.required' => 'The key field is required.',
            'items.*.key.min' => 'The key field must be at least 2 characters.',
            'items.*.key.max' => 'The key field may not be greater than 100 characters.',
            'items.*.value.required' => 'The value field is required.',
            'items.*.value.min' => 'The value field must be at least 2 characters.',
            'items.*.value.max' => 'The value field may not be greater than 100 characters.',
        ];
    }
}
