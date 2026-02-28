<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'conversation_id' => 'required',
            'type' => 'required',
            'message' => 'nullable',
            'template.id' => 'required_if:type,template|exists:templates,id',
            'file' => 'nullable',
            'files' => 'nullable|array',
            'files.*.file' => 'required',
            'caption' => 'nullable|string',
        ];
    }
}
