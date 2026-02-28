<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use App\Helpers\PlanPerks;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Group;
use App\Models\Campaign;
use App\Models\Template;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Whatsapp\App\Services\CampaignService;
use Modules\Whatsapp\App\Services\TemplateValidation;

class CampaignController extends Controller
{
    public $validation_params = [
        'name' => 'required|string|max:191',
        'platform_id' => 'required|exists:platforms,id',
        'group_id' => 'required|numeric|exists:groups,id',
        'message_type' => 'required|in:text,template,interactive',
        'template_id' => 'required_if:message_type,template',
        'message' => 'required_if:message_type,text',
        'send_type' => 'required|string|in:instant,draft,scheduled',
        'meta' => 'array',
    ];

    public $validation_message = [
        'name.required' => 'The name field is required',
        'platform_id.required' => 'The device field is required',
        'group_id.required' => 'The Group field is required',
        'message_type.required' => 'The message type field is required',
        'template_id.required' => 'The Template field is required',
        'meta.type.required' => 'The Interactive type is required',
        'meta.params' => 'The Interactive Params is required ',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = activeWorkspaceOwner()->campaigns()->whatsapp();

        $campaigns = $query->clone()
            ->filterOn(['name', 'status', 'message_type'])
            ->with(['group:id,name', 'template:id,name'])
            ->latest()
            ->paginate();

        PageHeader::set()->title('Campaigns')
            ->addLink('New Campaign', route('user.whatsapp.campaigns.create'), 'bx:plus')
            ->overviews([
                [
                    'icon' => "bx:grid-alt",
                    'title' => 'Total Campaigns',
                    'value' => $campaigns->total(),
                ],
                [
                    'icon' => "bx:file",
                    'title' => 'Draft Campaigns',
                    'value' => $query->clone()->whereStatus(Campaign::$STATUS_PENDING)->count(),
                ],
                [
                    'icon' => "bx:timer",
                    'title' => 'Scheduled Campaigns',
                    'value' => $query->clone()->whereStatus(Campaign::$STATUS_SCHEDULED)->count(),
                ],
                [
                    'icon' => "bx:check-circle",
                    'title' => 'Executed Campaigns',
                    'value' => $query->clone()->whereStatus(Campaign::$STATUS_SEND)->count(),
                ],
            ]);
        $systemTimezone = env('TIME_ZONE', 'UTC');

        return Inertia::render('Campaigns/Index', [
            'campaigns' => $campaigns,
            'systemTimezone' => $systemTimezone
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        validateWorkspacePlan('campaign');

        PageHeader::set()->title('Campaign Create')->buttons([
            [
                'url' => route('user.whatsapp.campaigns.index'),
                'text' => 'Back',
            ],
        ]);

        $user = activeWorkspaceOwner();

        $groups = $user->groups()
            ->whatsapp()
            ->select('id', 'name')
            ->latest()
            ->get();
        $devices = $user->platforms()
            ->whatsapp()
            ->select('id', 'name')
            ->latest()
            ->get();

        $time_zone_list = timezone_identifiers_list();

        return Inertia::render('Campaigns/Create', compact('groups', 'devices', 'time_zone_list'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ?Campaign $editCampaign = null)
    {
        validateWorkspacePlan('campaign');
        validateWorkspacePlan('cloud_messages');

        $user = activeWorkspaceOwner();

        $messageLimit = data_get($user->plan_data, 'cloud_messages.value', 0);

        $isUnlimited = $messageLimit === -1;
        if (!$isUnlimited) {
            $monthCycle = PlanPerks::calculateCurrentCycleUsage($user);
            $alreadySendMessageCount = $user->messages()
                ->whatsapp()
                ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                ->count();

            $remainingMessageCount = $messageLimit - $alreadySendMessageCount;
            $newMessageCount = $user->groups()->whatsapp()->find($request->group_id)?->customers()->count();

            if ($newMessageCount > $remainingMessageCount) {
                return back()->with('danger', "Message limit exceeded. Please upgrade your plan to send more messages.");
            }
        }

        $validated = $request->validate($this->validation_params);


        if ($request->send_type == 'scheduled') {

            $request->validate([
                'schedule_time' => 'required|date|after:now',
                'timezone' => 'required|timezone',
            ]);
            $validated['schedule_time'] = $request->schedule_time;
            $validated['timezone'] = $request->timezone;
        }

        $isTemplateMessage = in_array($request->message_type, ['template', 'interactive']);
        if ($isTemplateMessage) {
            TemplateValidation::validate($request);
        }

        if (isset($validated['schedule_time']) && isset($validated['timezone'])) {
            $schedule_at = Carbon::parse($request->schedule_time);
            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $schedule_at, $request->timezone);
            $dateTime = $dateTime->copy()->tz(env('TIME_ZONE', 'UTC'));

            $validated['schedule_at'] = $dateTime;
            $validated['timezone'] = $request->timezone;
            $validated['send_type'] = 'scheduled';

            $validated['meta']['schedule_timezone'] = $request->timezone;
            $validated['meta']['schedule_timestamp'] = $request->schedule_time;
        }

        $user = activeWorkspaceOwner();
        DB::beginTransaction();
        if ($request->get('message_type') == 'interactive' && $request->get('save_as_template')) {
            $template = Template::create([
                'module' => 'whatsapp',
                'platform_id' => $request->platform_id,
                'owner_id' => $user->id,
                'name' => $request->name,
                'type' => 'interactive',
                'meta' => $request->meta,
            ]);
            $validated['template_id'] = $template->id;
        }

        if ($request->message_type === 'text') {
            $validated['meta'] = [
                ...$validated['meta'],
                'type' => 'text',
                'text' => $request->message
            ];
        }

        $validated['module'] = 'whatsapp';
        $campaign = $user->campaigns()->updateOrCreate(
            [
                'id' => $editCampaign?->id ?? null,
            ],
            $validated
        );

        switch ($campaign->send_type) {
            case 'scheduled':
                $campaign->update([
                    'status' => Campaign::$STATUS_SCHEDULED,
                ]);
                $message = 'Campaign Scheduled Successfully';
                break;
            case 'draft':
                $message = 'Campaign Draft Saved Successfully';
                break;
        }

        if ($campaign->send_type == 'instant') {
            try {
                CampaignService::send($campaign);
            } catch (\Throwable $th) {
                DB::rollBack();
                return back()->with('danger', $th->getMessage());
            }
            $message = 'Campaign Sent Successfully';
        }

        DB::commit();

        return to_route('user.whatsapp.campaigns.index')
            ->with('success', $message);
    }

    public function show(Request $request, Campaign $campaign)
    {

        PageHeader::set()
            ->title('Campaign Logs')
            ->buttons([
                [
                    'url' => route('user.whatsapp.campaigns.index'),
                    'text' => 'Back',
                ],
            ]);

        $campaign = $campaign->loadCount([
            'logs as total_messages',
            'logs as failed_messages' => fn($q) => $q->whereNotNull('failed_at'),
            'logs as delivered_messages' => fn($q) => $q->whereNotNull('delivered_at'),
            'logs as sent_messages' => fn($q) => $q->whereNotNull('send_at'),
            'logs as read_messages' => fn($q) => $q->whereNotNull('read_at'),
        ]);
        $query = $campaign->logs();

        $overviews = [
            [
                'icon' => "bx:list-ul",
                'value' => $query->clone()->count(),
                'title' => 'Total',
            ],
            [
                'icon' => "bx:list-ul",
                'value' => $query->clone()->whereNotNull('send_at')->count(),
                'title' => 'Send',
            ],
            [
                'icon' => "bx:checkbox-checked",
                'value' => $query->clone()->whereNotNull('delivered_at')->count(),
                'title' => 'Delivered',
            ],
            [
                'icon' => "bx:timer",
                'value' => $query->clone()->whereNotNull('read_at')->count(),
                'title' => 'Read',
            ],
        ];
        PageHeader::set('Campaign Logs')->overviews($overviews);

        $logs = $campaign->logs()
            ->when(request()->filled('search'), function ($q) {
                $q
                    ->where('waid', 'LIKE', '%' . request('search') . '%')
                    ->orWhere('wamid', 'LIKE', '%' . request('search') . '%');
            })

            ->paginate();

        return Inertia::render('Campaigns/Logs', [
            'logs' => $logs,
            'campaign' => $campaign
        ]);
    }

    public function edit(Campaign $campaign)
    {
        PageHeader::set()->title('Campaign Edit')->buttons([
            [
                'url' => route('user.whatsapp.campaigns.index'),
                'text' => 'Back',
            ],
        ]);

        $groups = Group::select('id', 'name')
            ->whatsapp()
            ->latest()
            ->get();

        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();
        $devices = $user->platforms()
            ->whatsapp()
            ->select('id', 'name')
            ->latest()
            ->get();

        $time_zone_list = timezone_identifiers_list();

        return Inertia::render('Campaigns/Create', [
            'editCampaign' => $campaign,
            'groups' => $groups,
            'devices' => $devices,
            'time_zone_list' => $time_zone_list
        ]);
    }

    public function update(Request $request, Campaign $campaign)
    {
        return $this->store($request, $campaign);
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        return back()->with('success', __('Deleted Successfully'));
    }

    public function send(Campaign $campaign)
    {
        CampaignService::send($campaign);
        return back()->with('success', 'Campaign Sent Successfully');
    }

    public function copy(Campaign $campaign)
    {
        validateWorkspacePlan('campaign');

        $copy = $campaign->replicate();
        $copy->name = $copy->name . ' - Copy';
        $copy->status = Campaign::$STATUS_DRAFT;
        $copy->save();
        return to_route('user.whatsapp.campaigns.index')->with('success', 'Campaign Copied Successfully');
    }
}
