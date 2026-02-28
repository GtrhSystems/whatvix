<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Campaign, Customer, PlatformLog, User};
use Illuminate\Http\Request;
use Nwidart\Modules\Facades\Module;
use Carbon\CarbonInterface;
use Illuminate\Support\Number;

class UserDashboardController extends Controller
{
    protected $user;

    private function user()
    {
        if (request()->filled('user_id')) {
            return $this->user = User::find(request('user_id'));
        } else {
            return $this->user = activeWorkspaceOwner();
        }
    }
    private function activeModules()
    {
        $activeModules = collect(Module::allEnabled())->map(function ($module) {
            return [
                'name' => str($module->getName())->kebab()->toString(),
                'accessible' => $module->get('accessible', true),
                'module_type' => $module->get('module_type')
            ];
        })->filter(function ($module) {
            return $module['accessible'] && $module['module_type'] === 'platform';
        })->pluck('name');

        $hasModule = request()->filled('platform');

        if ($hasModule) {
            $activeModules = $activeModules->filter(fn($module) => $module === request('platform'));
        }

        return $activeModules->toArray();
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
        return [
            [
                'icon' => "bx:message",
                'title' => 'Messages',
                'style' => 'bg-teal-500 text-teal-600 bg-opacity-20',
                'value' => $this->user()->messages()
                  //  ->when($hasModule, fn($q) => $q->where('module', request('platform')))
                    ->count(),
            ],
            [
                'icon' => "ic:twotone-campaign",
                'title' => 'Campaigns',
                'style' => 'bg-danger-500 text-danger-600 bg-opacity-20',
                'value' => $this->user()->campaigns()->when($hasModule, fn($q) => $q->where('module', request('platform')))->count(),
            ],
            [
                'icon' => "bx:chat",
                'title' => request('platform') == 'whatsapp-web' ? 'Chat Limit' : 'Chats',
                'style' => 'bg-orange-500 text-orange-600 bg-opacity-20',
                'value' => request('platform') == 'whatsapp-web' ? 'unlimited' : $this->user()->conversations()
                //->when($hasModule, fn($q) => $q->where('module', request('platform')))
                ->count(),
            ],
            [
                'icon' => "hugeicons:contact-book",
                'title' => 'Contacts',
                'style' => 'bg-green-600 text-green-600 bg-opacity-20',
                'value' => $this->user()->customers()->when($hasModule, fn($q) => $q->where('module', request('platform')))->count(),
            ],
            [
                'icon' => "material-symbols:groups-2-outline",
                'title' => 'Groups',
                'style' => 'bg-violet-600 text-violet-600 bg-opacity-20',
                'value' => $this->user()->groups()->when($hasModule, fn($q) => $q->where('module', request('platform')))->count(),
            ],
            [
                'icon' => "bx:mobile",
                'title' => 'Devices',
                'style' => 'bg-info-500 text-info-600 bg-opacity-20',
                'value' => $this->user()->platforms()->when($hasModule, fn($q) => $q->where('module', request('platform')))->count(),
            ],

            [
                'icon' => "bx:grid-alt",
                'title' => 'Workspaces',
                'style' => 'bg-purple-600 text-purple-600 bg-opacity-20',
                'value' => $this->user()->myWorkspaces()->count(),
            ],
            [
                'icon' => "bx:group",
                'title' => 'Team Members',
                'style' => 'bg-pink-500 text-pink-600 bg-opacity-20',
                'value' => $this->user()->teamMembers()->count(),
            ],
        ];
    }

    private function getStorageSize()
    {
        return Number::fileSize($this->user()->assets()->sum('file_size'), 3);
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
                fn($q) => $q->where('owner_id', $this->user()->id)->where('module', $key)
            );
            $customerOverview[$key] = $customerData;
        }

        return $customerOverview;
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
                fn($q) => $q->where('owner_id', $this->user()->id)
                    ->where('status', Campaign::$STATUS_SEND)
                    ->where('module', $key)
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
                fn($q) => $q->where('owner_id', $this->user()->id)
                    ->where('module', $key)
            );
            $messageOverview[$key] = $messageData;
        }

        return $messageOverview;
    }
    public function getScheduleCampaign()
    {
        $scheduleCampaigns = Campaign::query()
            ->where('owner_id', activeWorkspaceOwnerId())
            ->whereBetween('schedule_at', [now()->yesterday(), now()->tomorrow()])
            ->limit(10)
            ->get();
        return $scheduleCampaigns;
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
            case 'schedule_campaigns':
                $response['schedule_campaigns'] = $this->getScheduleCampaign();
                break;

            case 'messages':
                $response['messages'] = $this->getMessageStats($filter);
                break;
            case 'storage':
                $response['storage'] = $this->getStorageSize();
                break;
            case 'plan_data':
                $plan = $this->user()->plan;
                $plan_data = array_merge($this->user()->plan_data ?? [], [
                    'plan_title' => $plan->title ?? '',
                ]);
                $response['plan_data'] = $this->user()->plan_id ? $plan_data : [];
                break;

            case 'all':
                $response = [
                    'overviews' => $this->getOverviews(),
                    'customers' => $this->getCustomerStats($filter),
                    'campaigns' => $this->getCampaignStats($filter),
                    'messages' => $this->getMessageStats($filter),
                ];
                break;

            default:
                return response()->json(['error' => 'Invalid analytics type'], 400);
        }

        return response()->json($response);
    }
}
