<?php

namespace App\Http\Requests;

use App\Models\QuickReply;
use Illuminate\Foundation\Http\FormRequest;

class QuickReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // if edit validate the user
        if ($this->method() == 'PUT') {
            /**
             * @var \App\Models\User
             */
            $user = auth()->user();
            $user->can('update:quick-replies', QuickReply::class);
        }
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
            'module' => 'required|string',
            'platform_id' => 'required|numeric|exists:platforms,id',
            'status' => 'required|string|in:active,inactive',
            'message_template' => 'required|string',
        ];
    }
}
