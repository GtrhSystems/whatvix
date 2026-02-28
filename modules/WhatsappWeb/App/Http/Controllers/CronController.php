<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use App\Models\Campaign;
use App\Helpers\PlanPerks;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Services\CampaignService;

class CronController extends Controller
{
    public function __invoke()
    {

        $scheduledCampaigns = Campaign::query()
            ->whatsappWeb()
            ->where('status', Campaign::$STATUS_SCHEDULED)
            ->where('schedule_at', '<=', now())
            ->get();

        $success = 0;
        $failed = 0;
        $result = [];

        if (count($scheduledCampaigns) > 0) {
            Campaign::query()
                ->where('status', Campaign::$STATUS_SCHEDULED)
                ->where('schedule_at', '<=', now())
                ->update(['status' => 'pending']);

            foreach ($scheduledCampaigns as $campaign) {

                $user = $campaign->owner;
                if (!$user) {
                    $campaign->update(['status' => 'failed']);
                    $failed++;
                    continue;
                }

                validateUserPlan('campaign', false, $user->id);
                validateUserPlan('web_messages', false, $user->id);

                try {

                    $monthCycle = PlanPerks::calculateCurrentCycleUsage($user);
                    $alreadySendMessageCount = $user->messages()->whatsappWeb()
                        ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                        ->count();

                    $messageLimit = data_get($user->plan_data, 'web_messages.value', 0);

                    if ($messageLimit !== -1) {
                        $remainingMessageCount = $messageLimit - $alreadySendMessageCount;
                        $newMessageCount = $user->groups()->whatsappWeb()->find($campaign->group_id)?->customers()->count();

                        if ($newMessageCount > $remainingMessageCount) {
                            $campaign->update(['status' => 'failed']);
                            $failed++;
                            continue;
                        }
                    }

                    $logs = CampaignService::send($campaign);

                    $result[] = [
                        'id' => $campaign->id,
                        'logs' => $logs
                    ];
                } catch (\Throwable $th) {
                  
                    $failed++;
                }
            }
        }

        $counter = [
            'proceed' => $scheduledCampaigns->count(),
            'published' => $success,
            'failed' => $failed,
            'result' => $result
        ];

        return $counter;
    }
}
