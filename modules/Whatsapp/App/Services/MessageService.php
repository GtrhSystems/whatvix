<?php

namespace Modules\Whatsapp\App\Services;

use App\Models\Message;
use App\Models\PlatformLog;
use Illuminate\Support\Facades\DB;
use App\Contracts\MessageServiceContract;

class MessageService implements MessageServiceContract
{

    protected array $shortCodes = [];

    public function __construct(public Message $message)
    {
        $customer = $message->customer;

        if ($customer) {
            $this->shortCodes = [
                '{name}' => $customer->name,
                '{phone}' => $customer->uuid,
            ];
        }

        // modify the message body for ai reply message structure
        if (is_string($message->getBody('text'))) {
            $this->message->body = [
                'body' => $message->getBody('text')
            ];
        }
    }

    /**
     * Send a message to a customer 
     * and store the message, platform log in the database.
     * then return the saved message
     *
     * @return Message
     */
    public function send(): Message
    {
        $newMessage = $this->message;

        $payload = $this
            ->shortCodeReplace()
            ->generatePayload();

        $platform = $this->message->platform;

        $accessToken = $platform->access_token;
        $phoneNumberId = $platform->uuid;

        // Check provider in meta
        $meta = $platform->meta ?? [];
        $provider = $meta['provider'] ?? 'meta';

        if ($provider === 'ycloud') {
            $res = YCloudClient::make(
                $accessToken,
                $phoneNumberId
            )->postMessage($payload);
        } else {
            $res = WhatsappClient::make(
                $accessToken,
                $phoneNumberId
            )->postMessage($payload);
        }

        if ($res->failed() && config('app.debug')) {
            throw new \Exception($res->json('error.message'));
        }

        // Set the uuid of the message to the id returned by whatsapp
        $newMessage->uuid = $res->json('messages.0.id');

        DB::beginTransaction();

        // Save the message to the database
        $newMessage->save();

        $newMessage->conversation->touch();

        // Log the message
        PlatformLog::create([
            'module' => 'whatsapp',
            'owner_id' => $newMessage->owner_id,
            'platform_id' => $newMessage->platform_id,
            'customer_id' => $newMessage->customer_id,
            'direction' => 'out',
            'message_type' => $newMessage->type,
            'message_text' => $newMessage->getText(),
            'meta' => $res->json(),
        ]);

        DB::commit();

        return $newMessage;
    }

    public function generatePayload(): array
    {
        $message = $this->message;
        $messageType = $message->type;
        $recipientId = $message->customer->uuid;
        $messageBody = $message->body;

        if ($messageType == 'interactive') {
            unset($messageBody['schedule_timezone'], $messageBody['schedule_timestamp']);
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            "recipient_type" => "individual",
            'to' => $recipientId,
            'type' => $messageType,
            $messageType => $messageBody,
        ];

        if (isset($message->meta['context']['id'])) {
            $payload['context']['message_id'] = $message->meta['context']['id'];
        }

        return $payload;
    }

    public function shortCodeReplace(): self
    {
        $modifiedBody = $this->message->body;

        if (isset($modifiedBody['body']) && is_string($modifiedBody['body'])) {
            $modifiedBody['body'] = self::replaceText($modifiedBody['body']);
        }

        $this->message->body = $modifiedBody;

        return $this;
    }

    public function replaceText(string $messageText): string
    {
        return str_replace(
            array_keys($this->shortCodes),
            array_values($this->shortCodes),
            $messageText
        );
    }
}
