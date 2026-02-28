<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Campaign;
use App\Models\Template;
use App\Helpers\PlanPerks;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Modules\WhatsappWeb\App\Services\CampaignService;

class CampaignController extends Controller
{
    public function index()
    {
        $query = activeWorkspaceOwner()->campaigns()->whatsappWeb();

        PageHeader::set()
            ->title('Campaigns')
            ->buttons([
                [
                    'url' => route('user.whatsapp-web.campaigns.create'),
                    'text' => 'Add New',
                ],
            ])->overviews(
                [
                    [
                        'icon' => "bx:list-ul",
                        'value' => $query->clone()->count(),
                        'title' => 'Total Campaigns'
                    ],
                    [
                        'icon' => "bx:circle",
                        'value' => $query->clone()->whereStatus(Campaign::$STATUS_PENDING)->count(),
                        'title' => 'Pending Campaigns'
                    ],
                    [
                        'icon' => "bx:timer",
                        'value' => $query->clone()->whereStatus(Campaign::$STATUS_SCHEDULED)->count(),
                        'title' => 'Scheduled Campaigns'
                    ],
                    [
                        'icon' => "bx:check-circle",
                        'value' => $query->clone()->whereStatus(Campaign::$STATUS_SEND)->count(),
                        'title' => 'Executed Campaigns'
                    ],
                ]
            );

        $campaigns = $query->filterOn(['name', 'status', 'message_type'])
            ->with(['platform:id,name', 'group:id,name', 'template:id,name'])
            ->latest()
            ->paginate();
        $systemTimezone = env('TIME_ZONE', 'UTC');
        return Inertia::render(
            'Campaigns/Index',
            compact('campaigns', 'systemTimezone')
        );
    }

    public function create(Request $request)
    {

        PageHeader::set()
            ->title('Create Campaign')
            ->buttons([
                [
                    'url' => route('user.whatsapp-web.campaigns.index'),
                    'text' => 'Back',
                ],
            ]);

        $platforms = activeWorkspaceOwner()->platforms()->whatsappWeb()->get(['id', 'name']);
        $templates = activeWorkspaceOwner()->templates()->whatsappWeb()->get();
        $groups = activeWorkspaceOwner()->groups()->whatsappWeb()->get(['id', 'name']);

        $time_zone_list = timezone_identifiers_list();

        return Inertia::render(
            'Campaigns/Create',
            compact(
                'platforms',
                'templates',
                'groups',
                'time_zone_list'
            )
        );
    }

    public function store(Request $request)
    {
        validateWorkspacePlan('campaign');
        validateWorkspacePlan('web_messages');

        $user = activeWorkspaceOwner();

        $messageLimit = data_get($user->plan_data, 'web_messages.value', 0);

        $isUnlimited = $messageLimit === -1;
        if (!$isUnlimited) {
            $monthCycle = PlanPerks::calculateCurrentCycleUsage($user);
            $alreadySendMessageCount = $user->messages()
                ->whatsappWeb()
                ->whereBetween('created_at', [$monthCycle['start'], $monthCycle['end']])
                ->count();

            $remainingMessageCount = $messageLimit - $alreadySendMessageCount;
            $newMessageCount = $user->groups()->whatsappWeb()->find($request->group_id)?->customers()->count();

            if ($newMessageCount > $remainingMessageCount) {
                return back()->with('dange  r', "Message limit exceeded. Please upgrade your plan to send more messages.");
            }
        }

        if (!$request->is_scheduled) {
            $request->merge([
                'schedule_at' => null,
            ]);
        }

        $validated = $request->validate([
            'module' => ['required', 'in:whatsapp-web'],
            'title' => 'required|max:200',
            'platform_id' => 'required|exists:platforms,id',
            'group_id' => 'required|numeric|exists:groups,id',
            'template_id' => ['required_if:message_type,template'],
            'message_type' => ['required', 'in:text,template'],
            'message_template' => ['required_if:message_type,text'],
            'is_scheduled' => ['required', 'boolean'],
            'schedule_at' => ['required_if:is_scheduled,true', 'date', 'after:now'],
            'delay_between' => ['required', 'array'],
        ]);


        if ($request->is_scheduled) {
            $date = Carbon::parse($request->schedule_at);

            $dateTime = Carbon::createFromFormat('Y-m-d H:i:s', $date, $request->timezone);
            $dateTime = $dateTime->copy()->tz(env('TIME_ZONE', 'UTC'));

            $validated['timezone'] = $request->timezone;
            $validated['schedule_at'] = $dateTime;
        } else {
            $validated['is_scheduled'] = false;
            $validated['schedule_at'] = null;
        }

        $campaign = new Campaign($validated);

        $payloadFromTemplate = $request->message_type == 'template' && $request->template_id;

        $template = new Template();
        if ($payloadFromTemplate) {
            $template = Template::find($request->template_id);
        }

        $payload = match ($request->input('message_type')) {
            'text' => ['text' => $request->input('message_template')],
            'template' => $template->meta,
        };

        $campaign->message_type = $payloadFromTemplate ? $template->type : 'text';
        $campaign->name = $request->input('title');
        $campaign->meta = $payload;
        $campaign->owner_id = activeWorkspaceOwnerId();
        $campaign->status = $request->is_scheduled ? 'scheduled' : 'pending';
        $campaign->saveOrFail();

        if ($campaign->status === 'scheduled') {
            $alertMessage = __('Campaign has been scheduled successfully');
        } else {
            try {
                CampaignService::send($campaign);
                $alertMessage = __('Campaign has been sent successfully');
            } catch (\Throwable $th) {
                return back()->with('danger', $th->getMessage());
            }
        }

        return to_route('user.whatsapp-web.campaigns.index')->with(
            'success',
            $alertMessage
        );
    }

    public function show(Campaign $campaign)
    {
        $query = $campaign->logs();

        PageHeader::set(
            'Campaign Logs',
            buttons: [
                [
                    'url' => route('user.whatsapp-web.campaigns.index'),
                    'text' => 'Back',
                ],
            ],
            overviews: [
                [
                    'icon' => "bx:grid-alt",
                    'value' => $query->clone()->count(),
                    'title' => 'Total Sent',
                ],
                [
                    'icon' => "bx:check-circle",
                    'value' => $query->clone()->whereNotNull('send_at')->count(),
                    'title' => 'Send',
                ],
                [
                    'icon' => "bx:check-double",
                    'value' => $query->clone()->whereNotNull('delivered_at')->count(),
                    'title' => 'Delivered',
                ],
                [
                    'icon' => "bx:show",
                    'value' => $query->clone()->whereNotNull('read_at')->count(),
                    'title' => 'Read',
                ],
            ]

        );

        $campaign = $campaign->loadCount([
            'logs as total_messages',
            'logs as failed_messages' => fn($q) => $q->whereNotNull('failed_at'),
            'logs as delivered_messages' => fn($q) => $q->whereNotNull('delivered_at'),
            'logs as sent_messages' => fn($q) => $q->whereNotNull('send_at'),
            'logs as read_messages' => fn($q) => $q->whereNotNull('read_at'),
        ]);

        return Inertia::render(
            'Campaigns/Logs',
            [
                'logs' => $campaign->logs()->with('customer:id,name,uuid')->paginate(),
                'campaign' => $campaign,
            ]
        );
    }

    public function edit(Campaign $campaign)
    {
        try {
            CampaignService::send($campaign);
        } catch (\Throwable $th) {
            return back()->with('danger', $th->getMessage());
        }
        return back()->with('success', __('Campaign has been sent successfully'));
    }

    public function destroy(Campaign $campaign)
    {
        $campaign->validateOwnership();
        $campaign->delete();
        return back()->with('success', __('Campaign has been deleted successfully'));
    }
}
