<?php

namespace App\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Events\IncomingNewConversationEvent;

class IncomingConversationJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $conversation
    ) {
      
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new IncomingNewConversationEvent($this->conversation))->toOthers();
    }
}
