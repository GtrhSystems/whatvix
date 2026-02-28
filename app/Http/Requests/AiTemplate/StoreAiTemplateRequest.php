<?php

namespace App\Http\Requests\AiTemplate;

use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiTemplateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        $isUpdate = $this->isMethod('PUT');

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'preview' => $isUpdate ? ['nullable'] : ['required', 'image', 'max:2048'],
            'icon' => $isUpdate ? ['nullable'] : ['required', 'image', 'max:2048'],
            'meta.provider' => 'required',
            'meta.model' => 'required',
            'meta.max_token' => 'required|integer',
            'meta.max_word' => 'required|integer',
            'status' => ['required'],
            'prompt' => 'required',
            'prompt_type' => 'required',
            'credit_charge' => 'required|numeric|between:0,999.99',
            'fields.*.type' => ['required'],
            'fields.*.name' => ['required'],
            'fields.*.placeholder' => ['required'],
            'fields.*.value' => ['nullable'],
        ];


        return $rules;
    }


    public function attributes()
    {
        return [
            'title' => 'Title',
            'description' => 'Description',
            'preview' => 'Preview Image',
            'icon' => 'Icon',
            'status' => 'Status',
            'prompt' => 'Prompt',
            'prompt_type' => 'Prompt Type',
            'credit_charge' => 'Credit Charge',
            'fields.*.type' => 'Field Type',
            'fields.*.name' => 'Field Name',
            'fields.*.placeholder' => 'Field Placeholder',
            'fields.*.value' => 'Field Value',
            'meta.provider' => 'AI Provider',
            'meta_model' => 'AI Model',
            'meta.max_token' => 'Max Token',
            'meta.max_word' => 'Max Word',
        ];
    }
}
