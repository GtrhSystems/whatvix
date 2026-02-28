<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()->role == 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'email' => ['required', 'email', 'max:50'],
            'password' => ['nullable', 'string', 'min:4'],
            'status' => ['required', 'boolean'],
            'credits' => ['required', 'integer'],
            'email_verified_at' => ['nullable', 'boolean'],
        ];
    }
}
