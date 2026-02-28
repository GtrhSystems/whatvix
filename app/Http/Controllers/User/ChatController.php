<?php

namespace App\Http\Controllers\User;

use App\Models\Chat;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Traits\Uploader;
use App\Helpers\PageHeader;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Services\ChatService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    use Uploader;
    public ?string $module = null;

    public function __construct()
    {
        if (request()->has('module')) {
            $this->module = request('module');
        }
    }

    public function index(?Conversation $conversation = null)
    {
        if (!$conversation) {
            PageHeader::set()->title("Conversations List");
        }

        $chatService = new ChatService(
            platform: $conversation?->platform
        );

        return $chatService->renderChatPage('Chats/Index', [
            'id' => $conversation?->id,
        ]);
    }

    public function show(Conversation $conversation)
    {
        PageHeader::set()->title("Conversation Details #{$conversation->id}");

        if ($conversation->messages()->unRead()->exists()) {
            $conversation->messages()->update(['status' => 'read']);
            $conversation->touch();
        }

        return $this->index($conversation);
    }

    public function update(Request $request, string $conversationId)
    {
        $updateType = $request->input('update_type');
        if ($request->filled('module')) {
            $this->module = $request->input('module');
        }

        match ($updateType) {
            'profile_picture' => $this->updateProfilePicture($request, $conversationId),
            'note' => $this->updateNote($request, $conversationId),
            'auto_reply' => $this->toggleAutoReply($conversationId),
            'name' => $this->updateName($request, $conversationId),
            default => abort(404),
        };

        return back();
    }

    private function updateProfilePicture(Request $request, $conversationId)
    {
        $request->validate([
            'picture' => ['required', 'image', 'max:2048'],
        ]);

        if ($this->module == 'whatsapp-web') {
           
        } else {
            $conversation = Conversation::findOrFail($conversationId);
            $customer = $conversation->customer;
            $customer->picture = $this->saveFile($request, 'picture');
            $customer->save();
        }

        Toastr::success(__('Profile Picture Updated Successfully'));
    }

    private function updateNote(Request $request, $conversationId)
    {
        $request->validate([
            'note' => 'required',
        ]);

        if ($this->module == 'whatsapp-web') {
            Chat::query()
                ->where('id', $conversationId)
                ->update(['description' => $request->input('note')]);
        } else {
            $conversation = Conversation::findOrFail($conversationId);
            $meta = $conversation->meta ?? [];
            $meta['note'] = $request->input('note');
            $conversation->meta = $meta;
            $conversation->save();
        }

        Toastr::success(__('Note Updated Successfully'));
    }

    private function toggleAutoReply($conversationId): bool
    {
        if ($this->module == 'whatsapp-web') {
            $chat = Chat::findOrFail($conversationId);
        } else {
            $chat = Conversation::findOrFail($conversationId);
        }

        Toastr::success(__('Auto Reply Toggled Successfully'));

        return $chat->update(['auto_reply_enabled' => !$chat->auto_reply_enabled]);
    }

    private function updateName(Request $request, $id)
    {
        request()->validate([
            'name' => 'required',
        ]);

        if ($this->module == 'whatsapp-web') {
            Chat::query()->where('id', $id)->update(['name' => $request->input('name')]);
        } else {
            $conversation = Conversation::findOrFail($id);
            $conversation->name = $request->input('name');
            $conversation->save();
        }

        Toastr::success(__('Name Updated Successfully'));
    }

    // api routes

    public function api(string $data)
    {
        $chatService = new ChatService();
        return match ($data) {
            'conversations' => $chatService->conversations(),
            'ai_templates' => $chatService->aiTemplates(),
            'quick_reply_templates' => $chatService->quickReplyTemplates(),
            'templates' => $chatService->templates(),
            'badges' => $chatService->badges(),
            'load_more_messages' => $chatService->loadMoreMessages(),
            'messages' => $chatService->loadMessages(),
            'load_more_conversations' => $chatService->loadMoreConversations(),
            default => response([], 404)
        };
    }

    public function assignBadge(Request $request, $chatId)
    {
        $request->validate([
            'badge_id' => ['required', 'exists:badges,id']
        ]);

        if ($this->module == 'whatsapp-web') {
            Chat::query()->where('id', $chatId)
                ->update(['badge_id' => $request->input('badge_id')]);
        } else {
            $chat = Conversation::findOrFail($chatId);
            $chat->badge_id = $request->input('badge_id');
            $chat->save();
        }

        return back()->with('success', 'Badge assigned successfully');
    }

    public function removeBadge(Request $request, $chatId)
    {
        if ($this->module == 'whatsapp-web') {
            Chat::query()->where('id', $chatId)
                ->update(['badge_id' => null]);
        } else {
            $chat = Conversation::findOrFail($chatId);
            $chat->badge_id = null;
            $chat->save();
        }

        return back()->with('success', 'Badge removed successfully');
    }
}
