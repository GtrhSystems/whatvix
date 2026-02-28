<?php

namespace Modules\WhatsappWeb\App\Jobs;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\WhatsappWeb\App\Services\WhatsAppWebService;

class SendMessageJob implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $sessionId,
        public string $jid,
        public array $message,
        public string $messageType,
        public string $sendType,
        public bool $isWelcomeMessage = false
    ) {

    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppWebService $chatService): void
    {
        $res = $chatService->sendMessage(
            $this->sessionId,
            $this->jid,
            $this->message,
            $this->messageType,
            $this->sendType
        );

        if ($res->successful() && $this->isWelcomeMessage) {
            Chat::query()
                ->where('sessionId', $this->sessionId)
                ->where('id', $this->jid)
                ->update([
                    'wlc_mgs_send_at' => now()
                ]);
        }
    }

}
