<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use App\Models\Campaign;
use App\Helpers\PlanPerks;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Modules\Whatsapp\App\Services\CampaignService;

class CronController extends Controller
{
    public function __invoke()
    {
        $now = Carbon::now()->toDateTimeString();

        $scheduledCampaigns = Campaign::query()
            ->whatsapp()
            ->where('status', 'scheduled')
            ->where('schedule_at', '<=', $now)
            ->get();

        if (count($scheduledCampaigns) > 0) {
            Campaign::query()
                ->where('status', 'scheduled')
                ->where('schedule_at', '<=', $now)
                ->update(['status' => 'pending']);

            foreach ($scheduledCampaigns as $campaign) {

                $user = $campaign->owner;
                if (!$user) {
                    $campaign->update(['status' => 'failed']);
                    continue;
                }

                validateUserPlan('campaign', false, $user->id);
                validateUserPlan('cloud_messages', false, $user->id);

                try {

                    $monthCycle = PlanPerks::calculateCurrentCycleUsage($user);
                    $alreadySendMessageCount = $user->messages()->whatsapp()
                        ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                        ->count();

                    $messageLimit = data_get($user->plan_data, 'cloud_messages.value', 0);
                    $remainingMessageCount = $messageLimit - $alreadySendMessageCount;
                    $newMessageCount = $user->groups()->whatsapp()->find($campaign->group_id)?->customers()->count();

                    if ($newMessageCount > $remainingMessageCount) {
                        $campaign->update(['status' => 'failed']);
                        continue;
                    }

                    CampaignService::send($campaign);
                } catch (\Throwable $th) {
                    $campaign->update(['status' => 'failed']);
                }
            }
        }


        return true;
    }
}
