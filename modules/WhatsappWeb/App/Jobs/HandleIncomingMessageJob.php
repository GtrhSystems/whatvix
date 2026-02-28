<?php

namespace Modules\WhatsappWeb\App\Jobs;

use App\Models\Platform;
use App\Events\LiveChatNotifyEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class HandleIncomingMessageJob implements ShouldQueue
{
    use Dispatchable;

    public ?Platform $platform;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $payload)
    {
        $this->platform = Platform::query()->where('uuid', $this->payload['sessionId'])->firstOrFail();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        LiveChatNotifyEvent::broadcast($this->payload, $this->platform->owner_id, 'whatsapp-web')->toOthers();
        try {
            $this->createPlatformLog();
            $this->handleAutoReply();
        } catch (\Throwable $th) {
            Log::error('whatsapp webhook error: ' . $th->getMessage());
          
        }
    }

    private function createPlatformLog()
    {
        $message = data_get($this->payload, 'data.messages');
        $messageText = data_get($message, 'message.conversation');
        $fromMe = data_get($message, 'key.fromMe', false);
        return $this->platform->logs()->create([
            'module' => 'whatsapp-web',
            'owner_id' => $this->platform->owner_id,
            'direction' => $fromMe ? 'out' : 'in',
            'message_type' => $this->guessMsgType(),
            'message_text' => $messageText,
            'meta' => $this->payload,
        ]);
    }

    private function handleAutoReply()
    {
        $message = data_get($this->payload, 'data.messages');
        $fromMe = data_get($message, 'key.fromMe', false);
        if (!$fromMe && isset($message['message']['conversation'])) {
            $messageText = data_get($message, 'message.conversation');
            $jid = data_get($message, 'key.remoteJid');
            HandleAutoReplyJob::dispatch($messageText, $this->platform, $jid);
        }
    }

    private function guessMsgType()
    {
        $message = data_get($this->payload, 'data.messages');
        $type = 'other';

        if (isset($message['message']['conversation'])) {
            $type = 'text';
        }
        if (isset($message['message']['audioMessage'])) {
            $type = 'audio';
        }
        if (isset($message['message']['imageMessage'])) {
            $type = 'image';
        }
        if (isset($message['message']['videoMessage'])) {
            $type = 'video';
        }
        if (isset($message['message']['stickerMessage'])) {
            $type = 'sticker';
        }

        return $type;

    }

}
