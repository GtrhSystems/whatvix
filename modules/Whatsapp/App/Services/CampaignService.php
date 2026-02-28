<?php
namespace Modules\Whatsapp\App\Services;

use App\Models\Message;
use App\Models\Campaign;
use App\Models\CampaignLog;
use App\Models\Conversation;
use App\Models\Template;
use Modules\Whatsapp\App\Services\MessageService;
use Modules\Whatsapp\App\Services\TemplateService;

class CampaignService
{
    public static function send(Campaign $campaign)
    {

        $customers = $campaign->group?->customers ?? [];

        ini_set('max_execution_time', count($customers) * 5);

        foreach ($customers as $customer) {
            sleep(1);
            $conversation = Conversation::firstOrCreate([
                'module' => 'whatsapp',
                'platform_id' => $campaign->platform_id,
                'owner_id' => $campaign->owner_id,
                'customer_id' => $customer->id,
            ], [
                'badge_id' => null,
                'auto_reply_enabled' => $campaign->platform->isAutoReplyEnabled(),
                'meta' => []
            ]);


            if (in_array($campaign->message_type, ['template', 'interactive'])) {
                $template = new Template([
                    "module" => $campaign->module,
                    "owner_id" => $campaign->owner_id,
                    "name" => data_get($campaign, 'meta.name'),
                    "meta" => $campaign->meta,
                    "type" => $campaign->message_type
                ]);

                $templateService = new TemplateService($template);
                $message = $templateService->generateMessage($conversation, $customer);
            } else {
                $message = new Message([
                    'module' => 'whatsapp',
                    'uuid' => null,
                    'conversation_id' => $conversation->id,
                    'platform_id' => $conversation->platform_id,
                    'customer_id' => $conversation->customer_id,
                    'owner_id' => $conversation->owner_id,
                    'direction' => 'out',
                    'type' => $campaign->message_type,
                    'body' => $campaign->meta,
                ]);
            }

            $messageService = new MessageService($message);
            $sendMessage = $messageService->send();

            CampaignLog::create([
                'module' => 'whatsapp',
                'owner_id' => $conversation->owner_id,
                'campaign_id' => $campaign->id,
                'message_id' => $sendMessage->id,
                'customer_id' => $customer->id,
                'meta' => [
                    'phone' => $customer->uuid,
                    'wamid' => $sendMessage->uuid
                ],
            ]);


        }

        $campaign->update(['status' => Campaign::$STATUS_SEND]);
    }
}