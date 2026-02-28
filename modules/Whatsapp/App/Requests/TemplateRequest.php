<?php

namespace Modules\Whatsapp\App\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class TemplateRequest extends FormRequest
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

        // default rules
        $rules = [
            'name' => ['required'],
            'message_type' => ['required'],
            'meta' => ['required', 'array'],
        ];

        // text rules
        if ($this->message_type == 'text') {
            $rules['meta.body'] = ['required', 'string', 'max:1024'];
        }

        // image rules
        if (in_array($this->message_type, ['image', 'video', 'document'])) {
            $rules['meta.link'] = ['required', 'string', 'max:255'];
            $rules['meta.caption'] = ['nullable', 'string', 'max:255'];
        }

        // interactive rules 
        if ($this->message_type == 'interactive') {
            // interactive validation
            $types = ['button', 'cta_url', 'product', 'list', 'product_list', 'catalog_message'];
            $headerTypes = ['image', 'text', 'document', 'video', 'audio'];
            $rules = [
                ...$rules,
                'name' => ['required'],
                'meta' => ['required', 'array'],
                'meta.type' => ['required', Rule::in($types)],

                // header
                'meta.header' => ['required_if:type,product_list', 'array'],
                'meta.header.type' => ['required', Rule::in($headerTypes)],

                // types
                'meta.header.text' => ['required_if:meta.header.type,text', 'string', 'max:60'],
                'meta.header.image.link' => ['required_if:meta.header.type,image'],
                'meta.header.video.link' => ['required_if:meta.header.type,video',],
                'meta.header.document.link' => ['required_if:meta.header.type,document'],

                // body
                'meta.body' => ['required_unless:meta.type,product', 'array'],
                'meta.body.text' => ['required', 'string', 'max:1024'],

                // footer
                'meta.footer' => ['nullable', 'array'],
                'meta.footer.text' => ['nullable', 'string', 'max:60'],

                # actions
                'meta.action' => ['required', 'array'],

                # buttons
                'meta.action.buttons' => ['required_if:meta.type,button', 'array'],

                # cta_url
                'meta.action.parameters.display_text' => ['required_if:meta.type,cta_url', 'string', 'max:60'],
                'meta.action.parameters.url' => ['required_if:meta.type,cta_url', 'url'],

                # product
                'meta.action.catalog_id' => ['required_if:meta.type,product,product_list'],
                'meta.action.product_retailer_id' => ['required_if:meta.type,product'],

                # list
                'meta.action.button' => ['required_if:meta.type,list', 'string', 'max:60'],
                'meta.action.sections' => ['required_if:meta.type,list,product_list', 'array'],

               

                # catalog_message
                'meta.action.thumbnail_product_retailer_id' => ['required_if:meta.type,catalog_message', 'string', 'max:60'],
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'meta' => 'components',
            'meta.type' => 'component type',
            'meta.body' => 'message',

            // header
            'meta.header.type' => 'header type',
            'meta.header.text' => 'header text',
            'meta.header.image' => 'header image',
            'meta.header.video' => 'header video',
            'meta.header.document' => 'header document',

            // body
            'meta.body.text' => 'body text',

            // footer
            'meta.footer.text' => 'footer text',

            // action
            'meta.action' => 'action',
            'meta.action.buttons' => 'action buttons',

        ];
    }
}
