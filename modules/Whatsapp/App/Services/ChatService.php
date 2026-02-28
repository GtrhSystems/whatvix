<?php

namespace Modules\Whatsapp\App\Services;

use App\Models\Message;
use App\Models\Template;
use App\Traits\Uploader;
use Illuminate\Support\Str;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    use Uploader;

    public static function sendMessage(Request $request): Message
    {
        $newMessage = self::generateMessage($request);
        $messageService = new MessageService($newMessage);
        return $messageService->send();
    }

    public static function generateMessage(Request $request): Message
    {
        $conversation = Conversation::findOrFail($request->get('conversation_id'));
        $messageType = self::getMessageType($request);
        $messageBody = self::generateMessageBody($request);
        $messageMeta = self::generateMessageMeta($request);

        if ($messageType == 'voice') {
            $messageType = 'audio';
        }

        if (in_array($messageType, ['template', 'interactive'])) {
            $template = new Template([
                "module" => 'whatsapp',
                "owner_id" => $conversation->owner_id,
                "name" => $request->input('template.name'),
                "meta" => $request->input('template.meta'),
                "type" => $messageType,
            ]);

            $customer = $conversation->customer;
            $templateService = new TemplateService($template, $conversation, $customer);
            $messageBody = $templateService->generateMessageBody();
            $messageMeta = $request->input('template.meta.components');
        }

        return new Message([
            'module' => 'whatsapp',
            'owner_id' => $conversation->owner_id,
            'platform_id' => $conversation->platform_id,
            'conversation_id' => $conversation->id,
            'customer_id' => $conversation->customer_id,

            'uuid' => null,
            'direction' => 'out',

            'type' => $messageType,
            'body' => $messageBody,
            'meta' => $messageMeta,
            'status' => 'sent',
        ]);
    }

    private static function generateMessageBody(Request $request): array
    {
        $type = self::getMessageType($request);
        $body = [];
        switch ($type) {
            case 'text':
                $body = [
                    'preview_url' => false,
                    'body' => $request->input('message'),
                ];
                break;

            case 'document':
            case 'audio':
            case 'voice':
            case 'video':
            case 'image':

                if (in_array($type, ['voice'])) {
                    $voiceUrl = self::uploadVoice($request->file('file'));
                    $body['link'] = $voiceUrl;
                } else {
                    $body['link'] = $request->collect('attachments')->first();
                    if (in_array($type, ['video', 'image', 'document'])) {
                        $body['caption'] = $request->input('caption');
                    }
                }

                break;

            case 'template':
                $template = $request->input('template');
                $components = $request->input('template.meta.components');
                $components = TemplateService::generateComponents($components);
                $body = [
                    'name' => $template['name'],
                    'language' => $template['meta']['language'],
                    'components' => $components,
                ];
                break;

            case 'interactive':
                $body = $request->input('template.meta');
                break;

            default:
                throw new \Exception("Message type not supported: $type");
        }

        return $body;
    }

    private static function generateMessageMeta(Request $request): array
    {
        $messageMeta = [];

        $messageType = self::getMessageType($request);

        if ($messageType == 'template') {
            $messageMeta = $request->input('template.meta.components');
        }

        if ($request->filled('context')) {
            $messageMeta['context'] = $request->input('context');
        }

        return $messageMeta;
    }

    private static function getMessageType(Request $request): string
    {
        return $request->input('template.type') ?? $request->input('type');
    }

    private static function uploadVoice(UploadedFile $voiceFile)
    {
        $extension = $voiceFile->extension() ?? 'wav';
        $randomString = Str::random(20);
        $directory = 'uploads' . date('/y') . '/' . date('m');
        $filePath = "$directory/$randomString.$extension";
        Storage::put($filePath, $voiceFile->get());
        return Storage::url($filePath);
    }
}
