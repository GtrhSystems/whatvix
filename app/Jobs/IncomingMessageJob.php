<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use App\Events\IncomingNewMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class IncomingMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $message
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        broadcast(new IncomingNewMessageEvent($this->message))->toOthers();
    }
}
