<?php

namespace Modules\Whatsapp\App\Services;

use Exception;
use App\Models\Message;
use App\Models\Customer;
use App\Models\Platform;
use App\Models\Conversation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Whatsapp\App\Models\CloudApp;
use Illuminate\Validation\ValidationException;

class CloudAppService
{
    protected $requiredParams = ['appkey', 'authkey', 'to', 'message'];

    public ?CloudApp $app = null;

    public function sendMessage(array $params)
    {
        try {
            $this->validateParams($params);
            $this->app = CloudApp::query()->where('key', $params['appkey'])->first();
            throw_unless($this->app, new Exception("Invalid appkey"));
            $verification = $this->app->user()->where('authkey', $params['authkey']);
            throw_unless($verification, new Exception("Invalid authkey"));
            throw_unless($this->app->platform, new Exception("app device not found"));
            DB::beginTransaction();
            $message = $this->generateMessage($params);
            $messageService = new MessageService($message);
            $sendMessage =  $messageService->send();

            $response = [
                'mid' => $sendMessage->uuid,
                'status' => $sendMessage->status
            ];

            $this->app->logs()->create([
                'owner_id' => $message->owner_id,
                'platform_id' => $message->platform_id,
                'to' => $message->customer->uuid,
                'status_code' => 200,
                'request' => $params,
                'response' => $response,
            ]);

            DB::commit();
            return $response;
        } catch (Exception $e) {
            throw new Exception("Error sending WhatsApp SMS: " . $e->getMessage());
        }
    }

    private function validateParams(array $params)
    {
        $validator = Validator::make($params, [
            'appkey' => 'required|string',
            'authkey' => 'required|string',
            'to' => 'required|string',
            'template_id' => 'sometimes|string',
            'message' => 'sometimes|string',
            'file' => 'sometimes|string',
            'variables' => 'sometimes|array',
            'sandbox' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $unknownParams = array_diff(array_keys($params), $this->requiredParams);
        if (!empty($unknownParams)) {
            throw new Exception("Unknown parameters: " . implode(', ', $unknownParams));
        }
    }

    private function generateMessage(array $params): Message
    {

        $customer = $this->resolveCustomer($params['to']);
        $conversation = $this->resolveConversation($customer);

        $message = new Message([
            'module' => 'whatsapp',
            'owner_id' => $conversation->owner_id,
            'platform_id' => $conversation->platform_id,
            'conversation_id' => $conversation->id,
            'customer_id' => $conversation->customer_id,

            'uuid' => null,
            'direction' => 'out',

            'type' => 'text',
            'body' => [
                'body' => $params['message']
            ], // will send as payload
            'meta' => [], // store for preview in chat
            'status' => 'sent',
        ]);


        return $message;
    }

    private function resolveCustomer(string $phoneNumber): Customer
    {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        $formattedNumber = $phoneUtil->parse($phoneNumber, "BD");
        $fullPhoneNumber = $formattedNumber->getCountryCode() . $formattedNumber->getNationalNumber();
        return Customer::firstOrCreate([
            'module' => 'whatsapp',
            'owner_id' => $this->app->user_id,
            'uuid' => $fullPhoneNumber,
        ], [
            'picture' => 'https://cdn-icons-png.flaticon.com/512/149/149071.png',
            'name' => $fullPhoneNumber,
            'meta' => [
                'dial_code' => $formattedNumber->getCountryCode(),
                'phone' => $formattedNumber->getNationalNumber(),
            ]
        ]);
    }

    private function resolveConversation(Customer $customer): Conversation
    {
        return Conversation::firstOrCreate([
            'module' => 'whatsapp',
            'platform_id' => $this->app->platform_id,
            'owner_id' => $this->app->user_id,
            'customer_id' => $customer->id,
        ], [
            'auto_reply_enabled' => $this->app->platform->isAutoReplyEnabled(),
            'meta' => []
        ]);
    }
}
