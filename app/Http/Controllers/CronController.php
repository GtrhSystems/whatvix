<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use App\Traits\Notifications;
use App\Services\CoinPaymentsAPI;

class CronController extends Controller
{
    use Notifications;

    public function notifyToUser($days = 7)
    {
      
        $willExpire = today()->addDays((int)$days)->format('Y-m-d');
      
        $users = User::whereHas('plan')->with('plan')
            ->where('will_expire', $willExpire)->latest()->get();

        foreach ($users as $key => $user) {
            $this->sentWillExpireEmail($user);
        }

        return "Cron job executed";
    }

    public function pruneActivityLog()
    {
        ActivityLog::whereDate('created_at', '<=', now()->subDays(30))->delete();
        return "Cron job executed";
    }
}
