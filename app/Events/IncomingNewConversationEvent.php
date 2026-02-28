<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class IncomingNewConversationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $owner_id = null;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public array $conversation,
    ) {
        $this->owner_id = $this->conversation['owner_id'] ?? null;
    }

    public function broadcastWith()
    {
        $conversation = Conversation::find($this->conversation['id']);
        return $conversation?->load('customer', 'owner', 'messages')->toArray() ?? [];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel("live-chat.whatsapp.{$this->owner_id}");
    }
}
