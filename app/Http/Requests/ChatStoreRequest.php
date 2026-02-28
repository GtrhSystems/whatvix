<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'conversation_id' => 'required',
            'type' => 'required',
            'message' => 'nullable',
            'template_id' => 'required_if:type,template|exists:templates,id',
            'file' => 'nullable',
            'files' => 'nullable|array',
            'files.*.file' => 'required',
            'caption' => 'nullable|string',
        ];
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
