<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Flow;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\WebScraping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\NumberChecker\App\Models\NumberScanner;

class PlanPerks
{
    private static $responseAsArray = [];

    private static function getResourceCount($user, string $planKey)
    {
        $monthCycle = self::calculateCurrentCycleUsage($user);
        return match ($planKey) {
            'devices' => $user->platforms()->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'web_messages' => $user->messages()->whatsappWeb()
                ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                ->count(),
            'cloud_messages' => $user->messages()->whatsapp()
                ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                ->count(),
            'chat_flow' => Flow::where('user_id', $user->id)->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'storage' => round($user->assets()->sum('file_size') / 1024 / 1024 / 1024, 4),
            'workspaces' => $user->myWorkspaces()->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'team_members' => $user->teamMembers()->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'ai_training' => $user->aiTrainings()->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'web_scrape' => WebScraping::where('user_id', $user->id)
                ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                ->sum('query_count'),
            'apps' => DB::table('cloud_apps')->union(DB::table('whatsapp_web_apps'), true)->where('user_id', $user->id)->count(),
            'contacts' => $user->customers()->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'custom_template' => $user->templates()->count(),
            'number_scanner' => NumberScanner::where('user_id', $user->id)
                ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])->count(),
            'campaign' => true,
            'auto_reply' => true,
            'quick_reply' => true,
            default => null,
        };
    }

    public static function checkPlan(string $planKey, bool $asArray = false, ?int $userId = null): array|bool
    {
        self::$responseAsArray = $asArray;

        $user = $userId ? User::findOrFail($userId) : Auth::user();

        if ($user->role === 'admin') {
            return self::resolveResponse(['status' => 'success', 'message' => __('You are an admin!')]);
        }

        $expirationDate = Carbon::parse($user->will_expire);
        if ($expirationDate->isPast()) {
            return self::resolveResponse(['status' => 'error', 'message' => 'Your plan has expired. Please renew your plan!']);
        }

        $resourceCount = self::getResourceCount($user, $planKey);

        if ($resourceCount === null) {
            return self::resolveResponse(['status' => 'error', 'message' => __('Plan key not exist or user data mismatch!')]);
        }

        if (!$user->plan_data) {
            return self::resolveResponse(['status' => 'error', 'message' => __('Please purchase a plan access this feature!')]);
        }

        $planValue = $user->plan_data[$planKey]['value'] ?? null;

        if ($planValue === -1) {
            return true;
        }

        if (is_bool($planValue) && boolval($planValue) === false) {
            return self::resolveResponse(['status' => 'error', 'message' => 'The feature is not available in your plan. Please upgrade your plan.']);
        }

        if (is_numeric($planValue) && $resourceCount && $resourceCount >= $planValue) {
            return self::resolveResponse(['status' => 'error', 'message' => 'You have reached your ' . $planKey . ' limit. Please upgrade your plan.']);
        }

        return true;
    }

    private static function resolveResponse($responseMsg)
    {
        throw_unless(
            self::$responseAsArray,
            new \App\Exceptions\SessionException(str($responseMsg['message'] ?? __('Something went wrong'))->headline())
        );

        return $responseMsg;
    }

    public static function calculateCurrentCycleUsage($user): array
    {
        // First check if user has an active subscription (purchased plan)
        $subscription = Order::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('will_expire', $user->will_expire)
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->first();

        // If subscription exists, use subscription data
        if ($subscription) {
            $plan = $subscription->plan;
            $startDate = Carbon::parse($subscription->created_at);
            $now = Carbon::now();
            $planDuration = $plan->days; // 30, 365, or 9999999

            $cycleStart = null;

            $subscriptionEndDate = $startDate->copy()->addDays($planDuration);

            if ($planDuration == 30) {
                $cycleStart = $startDate;
                $cycleEnd = $subscriptionEndDate;
            } elseif ($planDuration == 365) {
                $daysPassed = $startDate->diffInDays($now);
                $cyclesCompleted = floor($daysPassed / 30);
                $cycleStart = $startDate->copy()->addDays($cyclesCompleted * 30);
                $cycleEnd = $cycleStart->copy()->addDays(30);

                // Don't exceed subscription end
                if ($cycleEnd->greaterThan($subscriptionEndDate)) {
                    $cycleEnd = $subscriptionEndDate;
                }
            } else {
                $cycleStart = Carbon::now()->startOfMonth();
                $cycleEnd = $cycleStart->copy()->addDays(30);
            }

            return [
                'start' => $cycleStart,
                'end' => $cycleEnd,
            ];
        }

        if ($user->plan_id) {
            $plan = Plan::find($user->plan_id);
            if ($plan && $plan->is_trial == 1) {
                $startDate = Carbon::parse($user->created_at);
                $now = Carbon::now();
                $cycleEnd = $startDate->copy()->addDays($plan->trial_days);

                // Check if trial has expired
                if ($now->greaterThan($cycleEnd)) {
                    return self::resolveResponse(['status' => 'error', 'message' => __('Your trial has expired. Please upgrade your plan!')]);
                }

                return [
                    'start' => $startDate,
                    'end' => $cycleEnd,
                ];
            }
        }

        if (Carbon::parse($user->will_expire) > Carbon::now()) {
            return [
                'start' => Carbon::parse($user->created_at),
                'end' => Carbon::parse($user->will_expire),
            ];
        }

        return self::resolveResponse(['status' => 'error', 'message' => __('No active subscription found!')]);
    }

    public static function planValue($planKey, ?User $user = null)
    {
        if (!$user)
            $user = activeWorkspaceOwner();
        throw_unless($user, new \App\Exceptions\SessionException(__('User not found while checking plan value!')));
        $value = data_get($user->plan_data, $planKey . '.value', 'N/A');
        if ($value === -1)
            return 'Unlimited';
        return $value;
    }
}
