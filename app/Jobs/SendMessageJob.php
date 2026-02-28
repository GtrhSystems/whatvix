<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Queue\SerializesModels;
use App\Events\IncomingNewMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendMessageJob implements ShouldQueue
{
    use Queueable;


    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $messageAttrs,
        public $isWelcomeMsg = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = new Message($this->messageAttrs);
        $activeModuleName = ucfirst($message->module);
        $moduleMessageServiceClass = "Modules\\$activeModuleName\\App\\Services\\MessageService";
        throw_if(!class_exists($moduleMessageServiceClass), new \Exception("Chat service class: \"{$moduleMessageServiceClass}\" not found"));
        $messageService = new $moduleMessageServiceClass($message);
        $sendMessage = $messageService->send();

        if ($this->isWelcomeMsg) {
            $oldMeta = $sendMessage->conversation->meta ?? [];
            $sendMessage->conversation->update(['meta' => [...$oldMeta, 'wlc_message_send_at' => now()]]);
        }

        if ($sendMessage->uuid) {
            broadcast(new IncomingNewMessageEvent($sendMessage->toArray()));
        }
    }
}
