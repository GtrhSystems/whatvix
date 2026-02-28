<?php


namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Message;

class IncomingMessageUpdateEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $owner_id = null;
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Message $message,
    ) {
        $this->owner_id = $this->message?->owner_id ?? null;
    }

    public function broadcastWith()
    {
        return $this->message->toArray();
    }

    public function broadcastWhen(): bool
    {
        return $this->owner_id;
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
