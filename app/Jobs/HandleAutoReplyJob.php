<?php

namespace App\Jobs;

use App\Models\Message;
use App\Services\AutoReplyService;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleAutoReplyJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Message $incomingMessage
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $autoReplyService = new AutoReplyService($this->incomingMessage);
        $autoReplyService->sendWelcomeMessage();
        $autoReplyService->sendAutoReply();
    }
}
