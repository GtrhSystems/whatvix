<?php

namespace App\Http\Controllers\User;

use App\Helpers\PageHeader;
use Inertia\Inertia;
use App\Models\Gateway;
use App\Models\CreditLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AiTemplate;
use App\Models\CreditHistory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class CreditLogController extends Controller
{
    public function index(Request $request)
    {
        $totalCosts = CreditLog::query()->where('user_id', Auth::id())->sum('price');
        $totalCredits = CreditLog::query()->where('user_id', Auth::id())->sum('credits');
        $creditLogs = CreditLog::query()
            ->where('user_id', Auth::id())
            ->with(['user:id,name,created_at', 'gateway'])
            ->when($request->filled('status'), function ($query) {
                $status = match (request('status')) {
                    'complete' => 1,
                    default => 0,
                };
                $query->where('status', $status);
            })
            ->orderBy('created_at', in_array($request->order, ['desc', 'asc']) ? $request->order : 'desc')
            ->paginate(10);
        PageHeader::set(title: 'Credits')
            ->addLink('Back to Credits', route('user.credits.index'), 'bx:list-ol')
            ->addOverview('Total', $creditLogs->total(), 'bx:grid-alt')
            ->addOverview('Total Credits', $totalCredits, 'bx:grid-alt')
            ->addOverview('Total Costs', amount_format($totalCosts), 'bx:money');
        $gateways = Gateway::where('status', 1)
            ->select('id', 'name', 'currency', 'logo', 'charge', 'multiply', 'min_amount', 'max_amount', 'is_auto', 'image_accept', 'test_mode', 'status', 'phone_required', 'comment')
            ->get();

        $credit_fee = get_option('per_credit_fee');
        return Inertia::render('User/Credit/Log', [
            'creditLogs' => $creditLogs,
            'credit_fee' => $credit_fee,
            'gateways' => $gateways,
        ]);
    }

    public function history(Request $request)
    {
        $creditHistory = CreditHistory::query()
            ->where('user_id', Auth::id())
            ->with([
                'creditable' => function (MorphTo $morphTo) {
                    $morphTo->constrain([
                        AiTemplate::class => function ($query) {
                            $query->select('id', 'title');
                        },
                    ]);
                }
            ])
            ->orderBy('created_at', in_array($request->order, ['desc', 'asc']) ? $request->order : 'desc')
            ->paginate(10);

        $totalCharge = CreditHistory::query()->where('user_id', Auth::id())->sum('charge');
        $total = CreditHistory::query()->where('user_id', Auth::id())->count();
        $totalToday = CreditHistory::query()->where('user_id', Auth::id())
            ->whereDate('created_at', today())->count();
        PageHeader::set(title: 'Credits')
            ->addLink('Back to Credits', route('user.credits.index'), 'bx:list-ol')
            ->addOverview('Total', $total, 'bx:grid-alt')
            ->addOverview('Total Today', $totalToday, 'bx:calendar')
            ->addOverview('Total Charge', amount_format($totalCharge), 'bx:money');
        return Inertia::render('User/Credit/History', [
            'creditHistory' => $creditHistory,
        ]);
    }
}
