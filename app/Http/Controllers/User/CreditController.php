<?php

namespace App\Http\Controllers\User;

use App\Helpers\PageHeader;
use Inertia\Inertia;
use App\Models\Gateway;
use App\Traits\Uploader;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\AiGenerate;
use Illuminate\Support\Facades\Auth;

class CreditController extends Controller
{
    use Uploader;
    public function index()
    {
        $query = AiGenerate::where('user_id', Auth::id());
        PageHeader::set(title: 'Credits')
            ->addLink('Credit Logs', route('user.credit-logs.index'), 'bx:list-ol')
            ->addOverview('Total Credits', Auth::user()->credits, 'bx:grid-alt')
            ->addOverview('Today Use', $query->clone()->whereDate('created_at', today())->sum('charge'), 'bx:money')
            ->addOverview('Last 7 Days', $query->clone()->whereDate('created_at', '>=', now()->subDays(7))->sum('charge'), 'bx:money')
            ->addOverview('Last 30 Days', $query->clone()->whereDate('created_at', '>=', now()->subDays(30))->sum('charge'), 'bx:money');

        /** @var \App\Models\User */
        $user = Auth::user();

        $credits = $user->credits ?? 0;

        $activeChartFilterBtn = request('scope') ?? 'day';

        $dateFormat = match (request('scope')) {
            'year' => "%Y",
            'month' => "%M %Y",
            'week' => "%a",
            default => "%h:%i %p",
        };

        $costChartData = $query->selectRaw("DATE_FORMAT(ai_generates.created_at,'$dateFormat') as date, SUM(ai_generates.charge) as credit")

            ->when($activeChartFilterBtn == 'day', function ($query) {
                return $query->whereDate('created_at', today())
                    ->groupByRaw('HOUR(ai_generates.created_at)');
            })
            ->when($activeChartFilterBtn == 'week', function ($query) {
                $start = now()->startOfWeek(Carbon::SATURDAY);
                $end = now()->endOfWeek(Carbon::FRIDAY);
                return $query->whereBetween('created_at', [$start, $end])
                    ->groupByRaw('DAY(ai_generates.created_at)');
            })
            ->when($activeChartFilterBtn == 'month', function ($query) {
                $year = today()->year;
                return $query->whereYear('created_at', $year)->groupByRaw('MONTH(ai_generates.created_at)');
            })
            ->when($activeChartFilterBtn == 'year', function ($query) {

                return $query->groupByRaw('YEAR(ai_generates.created_at)');
            })
            ->get();

        $totalCostAmount = (int) $costChartData->sum('credit');

        $spendCreditAmount = (int) AiGenerate::query()
            ->where('user_id', Auth::id())
            ->whereMonth('created_at', now()->month)->groupByRaw('MONTH(ai_generates.created_at)')
            ->sum('charge');

        $availableCreditAmount = (int) Auth::user()->credits ?? 0;
        $total = $spendCreditAmount + $availableCreditAmount * 100;

        $credit_fee = get_option('per_credit_fee');
        $gateways = Gateway::where('status', 1)->select('id', 'name', 'currency', 'logo', 'charge', 'multiply', 'min_amount', 'max_amount', 'is_auto', 'image_accept', 'test_mode', 'status', 'phone_required', 'comment')->get();

        return Inertia::render('User/Credit/Index', compact(
            'credits',
            'costChartData',
            'activeChartFilterBtn',
            'credit_fee',
            'gateways',
            'totalCostAmount',
            'availableCreditAmount',
            'spendCreditAmount',
        ));
    }
}
