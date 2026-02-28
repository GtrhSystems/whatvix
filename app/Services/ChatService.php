<?php


namespace App\Services;

use App\Models\Badge;
use App\Models\Platform;
use App\Models\Template;
use App\Models\AiTemplate;
use App\Models\QuickReply;
use App\Models\Conversation;

class ChatService
{

    public function __construct(
        public ?string $activeModule = null,
        public ?Platform $platform = null
    ) {
    }

    public function api(string $data)
    {
        return match ($data) {
            'conversations' => $this->conversations(),
            'ai_templates' => $this->aiTemplates(),
            'quick_reply_templates' => $this->quickReplyTemplates(),
            'templates' => $this->templates(),
            'badges' => $this->badges(),
            'load_more_messages' => $this->loadMoreMessages(),
            'load_more_conversations' => $this->loadMoreConversations(),
            default => response([], 404)
        };
    }

    public function conversations()
    {
        return Conversation::query()
            ->when($this->activeModule, fn($q) => $q->where('module', $this->activeModule))
            ->when($this->platform, fn($q) => $q->where('platform_id', $this->platform->id))
            ->when(request('badge_id'), fn($q) => $q->where('badge_id', request('badge_id')))
            ->when(request('customer_name'), function ($query) {
                return $query->whereHas('customer', function ($q2) {
                    $customerName = request('customer_name');
                    return $q2->where('name', 'like', "%{$customerName}%");
                });
            })
            ->with([
                'customer',
                'owner',
                'messages' => fn($query) => $query->latest('id')->limit(5),
            ])
            ->withCount(['messages as unread_count' => fn($q) => $q->unRead()])
            ->latest('unread_count')
            ->latest('updated_at')
            ->limit(10)
            ->get();
    }

    public function aiTemplates()
    {
        return AiTemplate::query()
            ->where('status', 'active')
            ->withCount([
                'users as isBookmarked' => function ($query) {
                    $query->where('user_id', auth()->id());
                }
            ])
            ->latest()
            ->orderBy('isBookmarked', 'DESC')
            ->paginate();
    }

    public function quickReplyTemplates()
    {
        $hasAccess = validateWorkspacePlan('quick_reply', true);
        if (!$hasAccess) {
            return collect([]);
        }
        return QuickReply::query()->active()
            ->when($this->activeModule, fn($q) => $q->where('module', $this->activeModule))
            ->when($this->platform, fn($q) => $q->where('platform_id', $this->platform->id))
            ->get();
    }

    public function templates()
    {
        $moduleName = request('module');
        return Template::query()
            ->when($this->activeModule, fn($q) => $q->where('module', $this->activeModule))
            ->when($moduleName, fn($query) => $query->where('module', $moduleName))
            ->when($this->platform, fn($q) => $q->where('platform_id', $this->platform->id))
            ->orWhereNot('type', 'template')
            ->latest()
            ->get();
    }

    public function badges()
    {
        return Badge::query()
            ->when($this->platform, fn($q) => $q->where('user_id', $this->platform->owner_id))
            ->get();
    }

    public function loadMoreMessages()
    {

        $conversation = Conversation::findOrFail(request('conversation_id'));

        $limit = request()->input('limit', 2);
        $lastMessageId = request()->input('last_message_id');
        $messages = $conversation
            ->messages()
            ->when($this->activeModule, fn($q) => $q->where('module', $this->activeModule))
            ->when($this->platform, fn($q) => $q->where('platform_id', $this->platform->id))
            ->when($lastMessageId, function ($query) use ($lastMessageId) {
                $query->where('id', '<', $lastMessageId);
            })
            ->latest('id')
            ->limit($limit)
            ->get();

        return response()->json($messages);
    }

    public function loadMessages()
    {
        $conversation = Conversation::findOrFail(request('conversation_id'));

        $search = request()->input('search', null);
        return $conversation
            ->messages()
            ->when($this->activeModule, fn($q) => $q->where('module', $this->activeModule))
            ->when($this->platform, fn($q) => $q->where('platform_id', $this->platform->id))
            ->whereType('text')
            ->when($search, function ($query) use ($search) {
                $query->whereLike('body->body', "%{$search}%");
            })
            ->paginate();
    }

    public function loadMoreConversations()
    {

        $limit = request()->input('limit', 10);
        $lastConversationId = request()->input('last_conversation_id');
        $conversations = Conversation::query()
            ->when($this->activeModule, fn($q) => $q->where('module', $this->activeModule))
            ->when($this->platform, fn($q) => $q->where('platform_id', $this->platform->id))
            ->with([
                'customer',
                'owner',
                'messages' => fn($query) => $query->latest('id')->limit(2)
            ])
            ->withCount(['messages as unread_count' => fn($q) => $q->unRead()])
            ->when($lastConversationId, function ($query) use ($lastConversationId) {
                $query->where('id', '<', $lastConversationId);
            })
            ->latest('unread_count')
            ->latest('updated_at')
            ->limit($limit)
            ->get();

        return response()->json($conversations);
    }

    /**
     * Render the chat page with given props.
     *
     * @param string $inertiaView
     * @param array $props
     * @return \Inertia\Response
     */
    public function renderChatPage(string $inertiaView = 'Chats/Index', array $props = [])
    {
        return inertia($inertiaView, [
            'conversations' => $this->conversations(),
            'chat_templates' => $this->templates(),
            'quick_replies' => $this->quickReplyTemplates(),
            'ai_templates' => $this->aiTemplates(),
            'badges' => $this->badges(),
            ...$props
        ]);
    }
}
