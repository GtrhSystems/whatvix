<?php

namespace Modules\WhatsappWeb\App\Services;

use App\Models\Campaign;
use App\Models\CampaignLog;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CampaignService
{

    /**
     * Send a campaign to all customers in a group
     *
     * @param Campaign $campaign
     */
    public static function send(Campaign $campaign): array
    {
        ini_set('max_execution_time', count($campaign->group->customers) * $campaign->delay_between[1]);
        $customers = $campaign->group->customers;
        $whatsappClient = new WhatsAppWebService();

        $result = [];

        foreach ($customers as $key => $customer) {

            $randomDelay = Arr::random($campaign->delay_between);
            if ($key > 0) {
                sleep($randomDelay);
            }

            $payload = self::replaceShortCodes(
                $campaign->meta,
                [
                    '{name}' => $customer->name,
                ]
            );

            $sessionId = $campaign->platform->uuid;
            $jid = "{$customer->uuid}@s.whatsapp.net";

            $res = $whatsappClient->sendMessage(
                sessionId: $sessionId,
                jid: $jid,
                message: $payload,
                messageType: $campaign->message_type,
                type: 'number'
            );

            $result[] = [
                'to' => $customer->uuid,
                'success' => $res->successful()
            ];

            if ($res->failed() && env('APP_DEBUG')) {
                dd([
                    'request' => $payload,
                    'response' => $res->json(),
                ]);
            }


            if ($res->successful()) {

                DB::beginTransaction();

                $meta = [
                    'request' => $payload,
                    'response' => $res->json(),
                ];

                CampaignLog::create([
                    'module' => $campaign->module,
                    'owner_id' => $campaign->owner_id,
                    'campaign_id' => $campaign->id,
                    'customer_id' => $customer->id,
                    'meta' => $meta
                ]);

                $campaign->update(['status' => Campaign::$STATUS_SEND]);
                DB::commit();
            }
        }
        return $result;
    }

    /**
     * Replace short codes in the message body with actual values.
     *
     * @param  array  $payload
     * @param  array  $replaceCodes
     * @return array
     */
    public static function replaceShortCodes(array $payload, array $replaceCodes): array
    {
        if (isset($payload['text'])) {
            $payload['text'] = str_replace(array_keys($replaceCodes), array_values($replaceCodes), $payload['text']);
        }

        return $payload;
    }
}