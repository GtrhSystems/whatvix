<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlatformConfigRequest extends FormRequest
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
        $rules = [
            // Whether to send an auto reply
            'send_auto_reply' => ['required', 'boolean'],

            // The method to be used for auto reply
            'auto_reply_method' => [
                'required_if:send_auto_reply,true',
                'in:default,trained_ai,auto_response,chat_flow'
            ],

            // The model to be used if auto reply method is auto_response
            'auto_response' => [
                'required_if:auto_reply_method,auto_response',
                'nullable',
            ],

            'trained_ai' => [
                'required_if:auto_reply_method,trained_ai',
                'nullable',
            ],

            // The flow to be used if auto reply method is chat_flow
            'chat_flow' => [
                'required_if:auto_reply_method,chat_flow',
                'nullable'
            ],

            // Whether to send a welcome message
            'send_welcome_message' => ['required', 'boolean'],

            // The welcome message template
            'welcome_message_template' => [
                'required_if:send_welcome_message,true',
                'nullable',
                'max:2000'
            ],
        ];

        if ($this->has('name')) {
            $rules['name'] = ['required', 'string', 'max:255'];
        }

        if (in_array($this->module, ['whatsapp', 'telegram'])) {
            $rules['access_token'] = ['required', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function passedValidation()
    {
        if ($this->boolean('send_auto_reply') === false) {
            $this->merge(['auto_reply_method' => null]);
            $this->merge(['auto_response' => null]);
            $this->merge(['trained_ai' => null]);
            $this->merge(['chat_flow' => null]);
        }

        if ($this->boolean('send_welcome_message') === false) {
            $this->merge(['welcome_message_template' => null]);
        }
    }
}
