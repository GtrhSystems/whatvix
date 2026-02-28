<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AutoReplyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'platform_id' => 'required|exists:platforms,id',
            'message_type' => 'required|string|in:text,template',
            'keywords' => 'required|array',
            'status' => 'required|in:active,inactive',

            'message_template' => 'required_if:message_type,text',
            'template_id' => ['required_if:message_type,template'],
        ];

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
