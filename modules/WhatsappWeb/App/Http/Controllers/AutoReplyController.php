<?php

namespace Modules\WhatsappWeb\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\AutoReply;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AutoReplyController extends Controller
{
    public function index(Request $request)
    {
        $query = activeWorkspaceOwner()->autoReplies()->whatsappWeb();

        PageHeader::set()->title('Auto Replies')->buttons([
            [
                'url' => route('user.whatsapp-web.auto-replies.create'),
                'text' => 'Add New',
                'icon' => 'bx-plus'
            ]
        ])
            ->overviews([
                [
                    'icon' => "bx-list-ul",
                    'value' => $query->clone()->count(),
                    'title' => 'Total Replies'
                ],
                [
                    'icon' => "bx:history",
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                    'title' => 'Created In Last 7 Days'
                ],
                [
                    'icon' => "bx:calendar",
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                    'title' => 'Created In Last 30 Days'
                ]
            ]);

        $autoReplies = $query
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('keyword', 'like', "%{$request->search}%")
                    ->orWhereRelation('template', 'name', 'like', '%' . $request->search . '%');
            })
            ->with('platform:id,name', 'template:id,name')
            ->latest()
            ->paginate();

        return Inertia::render(
            'AutoReplies/Index',
            compact(
                'autoReplies',
            )
        );
    }

    public function create()
    {
        PageHeader::set()
            ->title('Add Auto Reply')
            ->buttons([
                [
                    'url' => route('user.whatsapp-web.auto-replies.index'),
                    'text' => 'Back'
                ]
            ]);

        $platforms = activeWorkspaceOwner()->platforms()->whatsappWeb()->get();
        $templates = activeWorkspaceOwner()->templates()->whatsappWeb()->get();
        $sort_codes = ['{name}'];

        return Inertia::render(
            'AutoReplies/Create',
            compact(
                'platforms',
                'sort_codes',
                'templates'
            )
        );
    }

    public function store(Request $request)
    {
        $validation_params = [
            'platform_id' => 'required|numeric|exists:platforms,id',
            'keywords' => 'required|array',
            'message_type' => 'required|in:text,template',
            'message_template' => 'required_if:message_type,text',
            'template_id' => 'required_if:message_type,template',
            'status' => 'required|in:active,inactive',
        ];

        $validation_message = [
            'keywords.required' => 'The keywords field is required',
            'platform_id.required' => 'The device field is required',
            'message.required' => 'The message field is required',
            'template_id.required' => 'The Template is required',
        ];

        $validated = $request->validate($validation_params, $validation_message);

        $validated['module'] = 'whatsapp-web';

        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();
        $user->autoReplies()->create($validated);

        return to_route('user.whatsapp-web.auto-replies.index')->with('success', __('Created Successfully'));
    }

    public function edit(Request $request, AutoReply $autoReply)
    {
        PageHeader::set()->title('Edit Auto Reply')->buttons([
            [
                'url' => route('user.whatsapp-web.auto-replies.index'),
                'text' => 'Back'
            ]
        ]);

        $platforms = activeWorkspaceOwner()
            ->platforms()
            ->whatsappWeb()
            ->get();

        $templates = activeWorkspaceOwner()->templates()->whatsappWeb()->get();

        $sort_codes = ['{name}'];

        return Inertia::render('AutoReplies/Create', compact('platforms', 'autoReply', 'sort_codes', 'templates'));
    }

    public function update(Request $request, AutoReply $autoReply)
    {
        $validation_params = [
            'platform_id' => 'required|numeric|exists:platforms,id',
            'keywords' => 'required|array',
            'message_type' => 'required|in:text,template',
            'message_template' => 'required_if:message_type,text',
            'template_id' => 'required_if:message_type,template',
            'status' => 'required|in:active,inactive',
        ];

        $validation_message = [
            'keywords.required' => 'The keywords field is required',
            'platform_id.required' => 'The device field is required',
            'message.required' => 'The message field is required',
            'template_id.required' => 'The Template is required',
        ];

        $validated = $request->validate($validation_params, $validation_message);

        $autoReply->update($validated);

        return to_route('user.whatsapp-web.auto-replies.index')
            ->with('success', 'Updated successfully');
    }

    public function destroy(AutoReply $autoReply)
    {
        $autoReply->delete();
        return back()->with('success', 'Deleted successfully');
    }
}
