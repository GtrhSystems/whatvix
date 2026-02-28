<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Asset, Campaign, Conversation, Customer, Group, Message, Order, Plan, Platform, PlatformLog, Workspace};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class AdminDashboardController extends Controller
{
    private function activeModules()
    {
        $activeModules = collect(Module::allEnabled())->map(function ($module) {
            return [
                'name' => (string) str($module->getName())->kebab(),
                'accessible' => $module->get('accessible', true),
                'module_type' => $module->get('module_type')
            ];
        })->filter(function ($module) {
            return $module['accessible'] && $module['module_type'] === 'platform';
        });
        $hasModule = request()->filled('platform');
        if ($hasModule) {
            $activeModules = $activeModules->filter(fn($module) => $module === request('platform'));
        }
        return array_column($activeModules->toArray(), 'name');
    }
    private function getChartOverview($modelClass, $filterBy, $dateColumn, $serviceColumn, ?\Closure $queryModifier = null)
    {
        $dateFormatMap = [
            'year' => "%Y",
            'month' => "%M %Y",
            'week' => "%a",
            'day' => "%h",
        ];

        $dateFormat = $dateFormatMap[$filterBy] ?? "%h";

        $query = $modelClass::query()
            ->selectRaw("DATE_FORMAT($dateColumn, '$dateFormat') as date, COUNT($serviceColumn) as total");

        if ($queryModifier instanceof \Closure) {
            $queryModifier($query);
        }

        if ($filterBy === 'day') {
            $query->whereDate($dateColumn, today())
                ->groupByRaw("HOUR($dateColumn)");
        } elseif ($filterBy === 'week') {
            $start = now()->startOfWeek(CarbonInterface::SATURDAY);
            $end = now()->endOfWeek(CarbonInterface::FRIDAY);
            $query->whereBetween($dateColumn, [$start, $end])
                ->groupByRaw("DAY($dateColumn)");
        } elseif ($filterBy === 'month') {
            $query->whereYear($dateColumn, now()->year)
                ->groupByRaw("MONTH($dateColumn)");
        } elseif ($filterBy === 'year') {
            $query->groupByRaw("YEAR($dateColumn)");
        }

        return $query->orderBy($dateColumn, 'asc')->get();
    }

    private function getOverviews()
    {
        $hasModule = request()->filled('platform');
        $currentMonthSales = Order::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
        return [
            [
                'icon' => "bx-cart",
                'title' => 'Total Orders',
                'style' => 'bg-yellow-500 text-yellow-600 bg-opacity-20',
                'value' => Order::count(),
            ],
            [
                'icon' => "bx-dollar-circle",
                'title' => 'Total Sales',
                'style' => 'bg-red-500 text-red-600 bg-opacity-20',
                'value' => amount_format(Order::sum('amount')),
            ],
            [
                'icon' => "bx-calendar",
                'title' => 'Current Month Sales',
                'style' => 'bg-emerald-500 text-emerald-600 bg-opacity-20',
                'value' => amount_format($currentMonthSales),
            ],
            [
                'icon' => "bx:message",
                'title' => 'Messages',
                'style' => 'bg-teal-500 text-teal-600 bg-opacity-20',
                'value' => Message::when($hasModule, fn($q) => $q->where('module', request('module')))
                    ->count(),
            ],
            [
                'icon' => "bx:microphone",
                'title' => 'Campaigns',
                'style' => 'bg-danger-500 text-danger-600 bg-opacity-20',
                'value' => Campaign::when($hasModule, fn($q) => $q->where('module', request('module')))->count(),
            ],
            [
                'icon' => "bx:chat",
                'title' => 'Chats',
                'style' => 'bg-orange-500 text-orange-600 bg-opacity-20',
                'value' => Conversation::when($hasModule, fn($q) => $q->where('module', request('module')))->count(),
            ],
            [
                'icon' => "bx:user",
                'title' => 'Contacts',
                'style' => 'bg-green-600 text-green-600 bg-opacity-20',
                'value' => Customer::when($hasModule, fn($q) => $q->where('module', request('module')))->count(),
            ],
            [
                'icon' => "bx:user",
                'title' => 'Groups',
                'style' => 'bg-violet-600 text-violet-600 bg-opacity-20',
                'value' => Group::when($hasModule, fn($q) => $q->where('module', request('module')))->count(),
            ],
            [
                'icon' => "bx:mobile",
                'title' => 'Devices',
                'style' => 'bg-info-500 text-info-600 bg-opacity-20',
                'value' => Platform::when($hasModule, fn($q) => $q->where('module', request('module')))->count(),
            ],

            [
                'icon' => "bx:grid-alt",
                'title' => 'Workspaces',
                'style' => 'bg-purple-600 text-purple-600 bg-opacity-20',
                'value' => Workspace::count(),
            ],
            [
                'icon' => "bx:group",
                'title' => 'Team Members',
                'style' => 'bg-pink-500 text-pink-600 bg-opacity-20',
                'value' => DB::table('workspace_users')->count(),
            ],
            [
                'icon' => "bx:memory-card",
                'title' => 'Storage Used',
                'style' => 'bg-pink-500 text-pink-600 bg-opacity-20',
                'value' => Number::fileSize(Asset::query()->sum('file_size')),
            ],
        ];
    }

    private function getCustomerStats($filterBy)
    {
        $activeModules = $this->activeModules();

        $customerOverview = [];
        foreach ($activeModules as $key) {
            $customerData = $this->getChartOverview(
                Customer::class,
                $filterBy,
                'customers.created_at',
                'customers.id',
                fn($q) => $q->where('module', strtolower($key))
            );
            $customerOverview[$key] = $customerData;
        }

        return $customerOverview;
    }
    private function getSalesStats($filterBy)
    {
        $dateFormatMap = [
            'year' => "%Y",
            'month' => "%M %Y",
            'week' => "%a",
            'day' => "%h",
        ];

        $dateFormat = $dateFormatMap[$filterBy] ?? "%h";
        $salesOverview = Order::query()
            ->selectRaw("DATE_FORMAT(orders.created_at,'$dateFormat') as date, SUM(orders.amount) as sales")
            ->when($filterBy == 'day', function ($query) {
                $query->whereDate('created_at', today())
                    ->groupByRaw('HOUR(orders.created_at)');
            })
            ->when($filterBy == 'week', function ($query) {
                $start = now()->startOfWeek(CarbonInterface::SATURDAY);
                $end = now()->endOfWeek(CarbonInterface::FRIDAY);
                $query->whereBetween('orders.created_at', [$start, $end])
                    ->groupByRaw('DAY(orders.created_at)');
            })
            ->when($filterBy == 'month', function ($query) {
                $query->whereYear('created_at', now()->year)
                    ->groupByRaw('MONTH(orders.created_at)');
            })
            ->when($filterBy == 'year', function ($query) {
                $query->groupByRaw('YEAR(orders.created_at)');
            })
            ->orderBy('orders.created_at', 'asc')
            ->get()->map(function ($query) {
                return [
                    'date' => $query->date,
                    'sales' => round($query->sales, 2),
                ];
            });

        return $salesOverview;
    }

    private function getCampaignStats($filterBy)
    {
        $activeModules = $this->activeModules();

        $campaignOverview = [];
        foreach ($activeModules as $key) {
            $campaignData = $this->getChartOverview(
                Campaign::class,
                $filterBy,
                'campaigns.created_at',
                'campaigns.id',
                fn($q) => $q
                    ->where('status', Campaign::$STATUS_SEND)
                    ->where('module', strtolower($key))
            );
            $campaignOverview[$key] = $campaignData;
        }

        return $campaignOverview;
    }

    private function getMessageStats($filterBy)
    {
        $activeModules = $this->activeModules();

        $messageOverview = [];
        foreach ($activeModules as $key) {
            $messageData = $this->getChartOverview(
                PlatformLog::class,
                $filterBy,
                'platform_logs.created_at',
                'platform_logs.id',
                fn($q) => $q
                    ->where('module', strtolower($key))
            );
            $messageOverview[$key] = $messageData;
        }

        return $messageOverview;
    }
    public function getPlans()
    {
        $mostOrderedPlan = Plan::query()
            ->select('id', 'title', 'price', 'days')
            ->where('status', 1)
            ->when(request('filter') === 'today', function ($query) {
                $query->whereHas('orders', function ($q) {
                    $q->whereDate('created_at', Carbon::today());
                });
            })
            ->when(request('filter') === 'month', function ($query) {
                $query->whereHas('orders', function ($q) {
                    $q->whereYear('created_at', Carbon::now()->year)
                        ->whereMonth('created_at', Carbon::now()->month);
                });
            })
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->first();

        $recentOrders = Order::whereHas('user')
            ->whereHas('plan')
            ->with('user:id,avatar,name,created_at', 'plan:id,title')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($query) {
                return [
                    'avatar'      => $query->user->avatar ? asset($query->user->avatar) : 'https://ui-avatars.com/api/?name=' . $query->user->name,
                    'name'        => $query->user->name,
                    'plan'        => $query->plan->title,
                    'invoice'     => $query->invoice_no,
                    'amount'      => amount_format($query->amount, 'icon'),
                    'created_at'  => $query->created_at->diffForHumans(),
                    'link'        => url('admin/order/' . $query->id),
                ];
            });
        $popularPlans = Plan::whereHas('orders')
            ->withCount('activeuser')
            ->withCount('orders')
            ->orderByDesc('orders_count')
            ->withSum('orders', 'amount')
            ->get()
            ->map(function ($query) {
                return [
                    'name'          => $query->days == 30 ? $query->title.' - monthly' : ($query->days == 365 ? $query->title.' - yearly' : $query->title.' - lifetime'),
                    'activeuser'    => number_format($query->activeuser_count),
                    'orders_count'  => number_format($query->orders_count),
                    'total_amount'  => amount_format($query->orders_sum_amount, 'icon'),
                ];
            });
        return [
            'most_ordered_plan' => $mostOrderedPlan,
            'recent_orders'     => $recentOrders,
            'popular_plans'     => $popularPlans
        ];
    }
    public function index(Request $request)
    {
        $analytics = $request->query('analytics', 'all');
        $filter = $request->query('filter', 'month');

        $response = [];

        switch ($analytics) {
            case 'overviews':
                $response['overviews'] = $this->getOverviews();
                break;

            case 'customers':
                $response['customers'] = $this->getCustomerStats($filter);
                break;

            case 'campaigns':
                $response['campaigns'] = $this->getCampaignStats($filter);
                break;

            case 'messages':
                $response['messages'] = $this->getMessageStats($filter);
                break;

            case 'plans':
                $response['most_ordered_plan'] = $this->getPlans()['most_ordered_plan'] ?? [];
                $response['recent_orders'] = $this->getPlans()['recent_orders'] ?? [];
                $response['popular_plans'] = $this->getPlans()['popular_plans'] ?? [];
                break;
            case 'mostOrderedPlan':
                $response['most_ordered_plan'] = $this->getPlans()['most_ordered_plan'] ?? [];
                break;

            case 'sales':
                $response['sales'] = $this->getSalesStats($filter);
                break;

            case 'all':
                $response = [
                    'overviews' => $this->getOverviews(),
                    'customers' => $this->getCustomerStats($filter),
                    'campaigns' => $this->getCampaignStats($filter),
                    'messages' => $this->getMessageStats($filter),
                    'sales' => $this->getSalesStats($filter),
                    'most_ordered_plan' => $this->getPlans()['most_ordered_plan'] ?? [],
                    'recent_orders' => $this->getPlans()['recent_orders'] ?? [],
                    'popular_plans' => $this->getPlans()['popular_plans'] ?? [],
                ];
                break;

            default:
                return response()->json(['error' => 'Invalid analytics type'], 400);
        }

        return response()->json($response);
    }
}
