<?php

namespace Modules\Whatsapp\App\Services;

use App\Models\Message;
use App\Models\Template;
use App\Traits\Uploader;
use App\Models\Conversation;
use App\Models\Customer;

class TemplateService
{
    use Uploader;

    public array $shortCodes = [];

    /**
     * Create a new TemplateService instance.
     *
     * @param Template $template The template to be sent.
     */
    public function __construct(
        public Template $template,
        public ?Conversation $conversation = null,
        public ?Customer $customer = null,
    ) {
    }

    /**
     * Generate a Message instance for the given conversation and customer.
     *
     * This method will set the short codes for the template and replace them in the message body.
     * The message body will be generated according to the template components.
     *
     * @param Conversation $conversation
     * @param Customer|null $customer
     * @return Message
     */
    public function generateMessage(Conversation $conversation, Customer $customer = null): Message
    {
        $this->conversation = $conversation;
        $this->customer = $customer ?? $conversation->customer;

        $messageBody = $this->generateMessageBody();
        $messageMeta = $this->template->meta;

        return new Message([
            'module' => 'whatsapp',
            'uuid' => null,
            'conversation_id' => $conversation->id,
            'platform_id' => $conversation->platform_id,
            'customer_id' => $conversation->customer_id,
            'owner_id' => $conversation->owner_id,
            'direction' => 'out',
            'sender_id' => auth()->id(),
            'type' => $this->template->type,
            'body' => $messageBody,
            'status' => 'sent',
            'meta' => $messageMeta,
        ]);
    }

    public function generateMessageBody(): array
    {
        return $this->setShortCodes()->replaceShortCodes()->generateMessagePayload();
    }

    /**
     * Generate the message payload for sending a template message.
     *
     * Given a template, this method generates the required payload for sending a template message.
     * The payload is an array of components, each containing the following keys:
     * - type: the type of the component (e.g. 'header', 'body', 'footer', etc.)
     * - parameters: an array of objects, each containing the following keys:
     *   - type: the type of the parameter (e.g. 'text', 'image', etc.)
     *   - {type}: the value of the parameter (e.g. the text string, the image URL, etc.)
     *
     * @return array The generated message payload.
     */
    public function generateMessagePayload(): array
    {

        $template = $this->template;

        if ($template->type == 'interactive') {
            return $template->meta;
        }

        return [
            'name' => $template->name,
            'language' => [
                'code' => $template->meta['language'],
            ],
            'components' => $this->generateComponents($template->meta['components']),
        ];

    }

    /**
     * Replaces short codes in the template's meta with the actual values
     *
     * If the template is of type "template", this method will go through each component
     * and if the component has an "example" key, it will replace the short codes in the
     * "example" with the actual values. If the "example" is an array, it will go through
     * each item in the array and if the item is an array, it will go through each value
     * in the array and replace the short codes.
     *
     * If the template is of type "interactive", this method will replace the short codes
     * in the "header", "body" and "footer" texts.
     *
     * @return Template The template with the replaced short codes
     */
    public function replaceShortCodes(): self
    {
        $templateType = $this->template->type;

        $newMeta = $this->template->meta;
        if ($templateType == 'template') {
            $newComponents = collect($newMeta['components'])->map(function ($component) {
                if (isset($component['example']) && count($component['example'])) {
                    $component['example'] = collect($component['example'])
                        ->map(function ($item) {

                            if (is_array($item)) {
                                return collect($item)->map(function ($value) {
                                    if (is_array($value)) {
                                        return collect($value)->map(function ($val) {
                                            return self::replaceText($val);
                                        })->toArray();
                                    }
                                    return self::replaceText($value);
                                })->toArray();
                            }

                            if (isset($item['text'])) {
                                $item['text'] = self::replaceText($item['text']);
                            }
                            return $item;
                        })->toArray();
                }
                return $component;
            })->toArray();
            $newMeta['components'] = $newComponents;
        }

        if ($templateType == 'interactive') {
            if (isset($newMeta['header']['text'])) {
                $newMeta['header']['text'] = self::replaceText($newMeta['header']['text']);
            }

            if (isset($newMeta['body']['text'])) {
                $newMeta['body']['text'] = self::replaceText($newMeta['body']['text']);
            }

            if (isset($newMeta['footer']['text'])) {
                $newMeta['footer']['text'] = self::replaceText($newMeta['footer']['text']);
            }
        }

        $this->template->meta = $newMeta;
        return $this;
    }

    /**
     * Sets the short codes that will be replaced in the message.
     *
     * Currently, the following short codes are supported:
     * - {name}
     * - {phone}
     *
     * These short codes will be replaced with the actual value of the customer's name and phone number.
     *
     * @return $this
     */
    public function setShortCodes()
    {
        $name = $this->customer->name;
        $phone = $this->customer->meta['dial_code'] . $this->customer->meta['phone'];
        $this->shortCodes = [
            '{name}' => $name,
            '{phone}' => $phone,
        ];

        return $this;
    }

    /**
     * Replaces short codes in the given message text with their corresponding values.
     *
     * This method iterates over the short codes array, replacing each occurrence of a short code
     * in the message text with its corresponding value. The short codes and their values are set
     * using the setShortCodes method.
     *
     * @param string $messageText The text containing short codes to be replaced.
     * @return string The text with short codes replaced by their corresponding values.
     */
    public function replaceText(string $messageText): string
    {
        return str_replace(
            array_keys($this->shortCodes),
            array_values($this->shortCodes),
            $messageText
        );
    }


    /**
     * Generate the components array as required by the Whatsapp API.
     *
     * Given an array of components, this method filters out any components that do not have an
     * 'example' key, and then maps over the remaining components to create the required payload
     * for the Whatsapp API.
     *
     * The payload is an array of objects, each containing the following keys:
     * - type: the type of the component (e.g. 'header', 'body', 'footer', 'button')
     * - parameters: an array of objects, each containing the following keys:
     *   - type: the type of the parameter (e.g. 'text', 'image', etc.)
     *   - {type}: the value of the parameter (e.g. the text string, the image URL, etc.)
     *
     * Buttons are handled separately, and are added to the components array as separate objects.
     * Each button object has the following keys:
     * - type: 'button'
     * - sub_type: the type of the button (e.g. 'quick_reply', 'url', etc.)
     * - index: the index of the button in the buttons array
     * - parameters: an array of objects, each containing the following keys:
     *   - type: 'payload'
     *   - payload: the value of the button (e.g. the text string, the URL, etc.)
     *
     * @param array $components The array of components to process.
     * @return array The processed components array.
     */
    public static function generateComponents(array $components): array
    {
        $payloadComponents = collect($components)
            ->filter(function ($item) {
                return isset($item['example']);
            })
            ->map(function ($item) {
                // for header body footer
                if (empty($item['example']))
                    return null;

                $parameters = collect($item['example'] ?? [])
                    ->flatten()
                    ->map(function ($value) use ($item) {

                        $itemType = strtolower($item['type']);

                        $payloadType = 'text';
                        $payload = $value;

                        if (
                            $itemType == 'header' && isset($item['format']) &&
                            in_array(
                                strtolower($item['format']),
                                ['audio', 'document', 'image', 'video']
                            )
                        ) {
                            $payloadType = strtolower($item['format']);
                            $payload = [
                                'link' => $value
                            ];
                        }

                        return [
                            'type' => $payloadType,
                            $payloadType => $payload,
                        ];
                    })->toArray();

                return [
                    ...[
                        'type' => strtolower($item['type']),
                        'parameters' => $parameters
                    ]
                ];

            });

        // push buttons
        collect($components)
            ->filter(function ($item) {
                return isset($item['type']) && strtolower($item['type']) == 'buttons';
            })
            ->each(function ($item) use ($payloadComponents) {
                collect($item['buttons'])
                    ->filter(function ($button) {
                        return isset($button['example']);
                    })
                    ->each(function ($button, $index) use ($payloadComponents) {
                        $payloadComponents->push([
                            'type' => 'button',
                            'sub_type' => strtolower($button['type']),
                            'index' => $index,
                            'parameters' => collect($button['example'])->map(function ($value) {
                                return [
                                    'type' => 'payload',
                                    'payload' => $value,
                                ];
                            })->toArray(),
                        ]);
                    })->toArray();
            });

        return $payloadComponents->toArray();

    }
}
