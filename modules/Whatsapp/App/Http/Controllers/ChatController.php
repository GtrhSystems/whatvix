<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Platform;
use App\Helpers\PageHeader;
use App\Models\Conversation;
use App\Services\ChatService;
use Nwidart\Modules\Facades\Module;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function show($platformUuid, $conversation_id = null)
    {
        $platform = Platform::where('uuid', $platformUuid)->firstOrFail();
        PageHeader::set()->title('Messenger Chats')->buttons([
            [
                'text' => 'Back',
                'url' => route('user.whatsapp.platforms.index'),
            ],
        ]);

        $chatService = new ChatService('whatsapp', $platform);
        $languages = json_decode(file_get_contents(base_path('database/json/languages.json')), true);
        $languages = array_values(array_map(function ($language) {
            return [
                'id' => $language['id'],
                'name' => $language['name'],
            ];
        }, $languages));

        $moduleFeatures = Module::find('Whatsapp')->get('features', [
            'voice_messages' => false,
        ]);

        return Inertia::render('Chats/Index', [
            'id' => $conversation_id,
            'platform' => $platform,
            'conversations' => $chatService->conversations(),
            'chat_templates' => $chatService->templates(),
            'quick_replies' => $chatService->quickReplyTemplates(),
            'badges' => $chatService->badges(),
            'languages' => $languages,
            'module_features' => $moduleFeatures
        ]);
    }

    public function showConversation($platform_uuid, $conversation_id)
    {
        $platform = Platform::whereAny(['id', 'uuid'], $platform_uuid)->firstOrFail();

        // check if platform is by id then redirect it by uuid
        if ($platform->id === intval($platform_uuid)) {
            return to_route('user.whatsapp.platforms.conversations.show', [
                'platform_uuid' => $platform->uuid,
                'conversation_id' => $conversation_id
            ]);
        }

        $conversation = Conversation::findOrFail($conversation_id);

        PageHeader::set()->title("Conversation Details #{$conversation->id}");
        if ($conversation->messages()->unRead()->exists()) {
            $conversation->messages()->update(['status' => 'read']);
            $conversation->touch();
        }

        return $this->show($platform->uuid, $conversation->id);
    }
}
