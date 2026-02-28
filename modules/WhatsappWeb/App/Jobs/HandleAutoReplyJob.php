<?php

namespace Modules\WhatsappWeb\App\Jobs;

use App\Models\Chat;
use App\Models\Platform;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\WhatsappWeb\App\Services\AutoReplyService;

class HandleAutoReplyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $messageText,
        public Platform $platform,
        public string $jid
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $chat = Chat::query()
            ->where('sessionId', $this->platform->uuid)
            ->where('id', $this->jid)
            ->first();

        if (!$chat) {
            return;
        }

        $autoReplyService = new AutoReplyService(
            $this->messageText,
            $this->platform,
            $chat
        );
        $autoReplyService->handleAutoReply();
    }
}
