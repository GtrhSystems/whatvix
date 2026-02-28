<?php

namespace Modules\Whatsapp\App\Services;

use Illuminate\Http\Request;

class TemplateValidation
{
    public static function validate(Request $request, $validation_params = [], $validation_message = []): array
    {
        if ($request->message_type == 'template') {
            $templateValidationRules = [
                'meta.*.example.header_text.*' => 'required_if:message_type,template', // header text
                'meta.*.example.header_handle.*' => 'required_if:message_type,template', // header files
                'meta.*.example.body_text.*.*' => 'required_if:message_type,template', // body text
                'meta.*.example.body_handle.*' => 'required_if:message_type,template', // body files
                'meta.*.buttons.*.example.*' => 'required_if:message_type,template', // button values
            ];

            $templateValidationMessage = [
                'meta.*.example.header_text.*' => 'The header text field is required',
                'meta.*.example.header_handle.*' => 'The header file field is required',
                'meta.*.example.body_text.*.*' => 'The body text field is required',
                'meta.*.example.body_handle.*' => 'The body file field is required',
                'meta.*.buttons.*.example.*' => 'The button value field is required',
            ];

            $validation_params = array_merge($validation_params, $templateValidationRules);
            $validation_message = array_merge($validation_message, $templateValidationMessage);

        }

        if ($request->message_type == 'interactive') {

        }

        return $request->validate($validation_params, $validation_message);
    }

    /**
     * Return the validation rules for a given type
     *
     * @param string $type
     * @param bool $is_required
     * @return string
     */
    protected static function getValidationRulesFor(string $type, bool $is_required = false): string
    {
        $rules = [
            'image' => 'mimes:jpeg,png|max:5120',  // 5MB Max. Supported formats: JPEG, PNG
            'audio' => 'mimes:ogg,mp3,mpeg,amr,acc|max:16000', // 16MB Max. Supported formats: OGG & MP3
            'striker' => 'mimes:webp|max:100', // 100 kb Max. Supported formats:  WEBP
            'document' => 'mimes:doc,docx,xls,xlsx,ppt,pptx,pdf|max:50000',//  50MB Max. Supported formats: DOC, DOCX, XLS, PDF
        ];

        $required = $is_required ? 'required' : 'nullable';

        return isset($rules[$type]) ? $required : 'nullable';
    }
}
