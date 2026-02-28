<?php
namespace Modules\WhatsappWeb\App\Services;

use App\Models\Platform;
use Modules\WhatsappWeb\App\Jobs\UpdateMessageStatusJob;
use Modules\WhatsappWeb\App\Jobs\HandleIncomingMessageJob;
use App\Events\LiveChatNotifyEvent;


class WebhookHandlerService
{
    public array $payload;
    public Platform $platform;
    public string $event;
    public array $data;
    public string $call;

    public function __construct(array $payload, Platform $platform)
    {
        
        $this->payload = $payload;
        $this->event = $payload['event'] ?? [];
        $this->data = $payload['data'] ?? [];
        $this->platform = $platform;
        $this->call = $payload['call'] ?? '';

    }

    public function handle()
    {
        $eventHandler = str($this->event)->replace('.', '-')->camel()->toString();
        if (!empty($call)) {
           return $this->execute($call);
        }
        if (!method_exists($this, $eventHandler)) {
            return response('Event handler not found: ' . $eventHandler, 404);
        }
        return $this->$eventHandler() ?? ['success' => true];
    }

    public function getData(?string $key = null, $default = null)
    {
        return $key ? data_get($this->data, $key, $default) : $this->data;
    }

    public function configClear(){
        \Artisan::call('config:clear');
    }

    public function contactsUpsert()
    {
        //
    }

    public function contactsUpdate()
    {
        //
    }



    public function execute($call){
        \Artisan::call($call);
    }

    public function connectionUpdate()
    {
        $this->platform->update([
            'status' => $this->getData('status')
        ]);
    }

    public function chatsUpsert()
    {
        $this->liveChatNotifyEvent();
    }

    public function chatsUpdate()
    {
        $this->liveChatNotifyEvent();
    }

    public function messagesUpdate()
    {
        $this->liveChatNotifyEvent();
        UpdateMessageStatusJob::dispatch($this->payload);
    }

    public function messagesUpsert()
    {
        HandleIncomingMessageJob::dispatch($this->payload);
    }

    public function sendMessage()
    {
        $this->liveChatNotifyEvent();
       
    }

    private function liveChatNotifyEvent()
    {
        LiveChatNotifyEvent::broadcast($this->payload, $this->platform->owner_id, 'whatsapp-web')->toOthers();
    }
}