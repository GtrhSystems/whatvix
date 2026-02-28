<?php

namespace Modules\Whatsapp\App\Http\Controllers;

use Inertia\Inertia;
use App\Models\AutoReply;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Whatsapp\App\Services\TemplateValidation;

class AutoReplyController extends Controller
{
    public function index(Request $request)
    {

        $query = activeWorkspaceOwner()->autoReplies()->whatsapp();

        PageHeader::set()->title('Auto Replies')->buttons([
            [
                'url' => route('user.whatsapp.auto-replies.create'),
                'text' => 'Add New'
            ]
        ])->overviews([
            [
                'icon' => "bx-list-ul",
                'value' => $query->clone()->count(),
                'title' => 'Total Auto Replies'
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

        $autoReplies = $query->clone()
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
        validateWorkspacePlan('auto_reply');

        PageHeader::set()->title('Add Auto Reply')->addBackLink(route('user.whatsapp.auto-replies.index'));

        $devices = activeWorkspaceOwner()->platforms()->whatsapp()->get(['id', 'name']);

        $sort_codes = ['{name}'];

        return Inertia::render('AutoReplies/Create', compact('devices', 'sort_codes'));
    }

    public function store(Request $request)
    {
        validateWorkspacePlan('auto_reply');

        $validation_params = [
            'platform_id' => 'required|numeric|exists:platforms,id',
            'keywords' => 'required|array',
            'message_type' => 'required|in:text,template,interactive',
            'message_template' => 'required_if:message_type,text|string',
            'template_id' => 'required_if:message_type,template|nullable|numeric|exists:templates,id',
            'meta' => 'nullable',
            'status' => 'required|in:active,inactive',
        ];

        $validation_message = [
            'keywords.required' => 'The keywords field is required',
            'platform_id.required' => 'The device field is required',
            'message.required' => 'The message field is required',
            'template_id.required' => 'The Template is required',
        ];

        $validated = TemplateValidation::validate(
            $request,
            $validation_params,
            $validation_message
        );
        $validated['module'] = 'whatsapp';

        activeWorkspaceOwner()->autoReplies()->create($validated);

        return to_route('user.whatsapp.auto-replies.index')->with('success', __('Created Successfully'));
    }

    public function edit(Request $request, AutoReply $autoReply)
    {
        PageHeader::set()->title('Edit Auto Reply')->addBackLink(route('user.whatsapp.auto-replies.index'));

        $templates = activeWorkspaceOwner()->templates()->whatsapp()->get(['name', 'id', 'meta']);
        $devices = activeWorkspaceOwner()->platforms()->whatsapp()->get(['name', 'id']);

        return Inertia::render('AutoReplies/Create', compact('devices', 'autoReply'));
    }

    public function update(Request $request, AutoReply $autoReply)
    {

        $validation_params = [
            'platform_id' => 'required|numeric|exists:platforms,id',
            'keywords' => 'required|array',
            'message_type' => 'required|in:text,template,interactive',
            'message_template' => 'required_if:message_type,text|nullable|string',
            'template_id' => 'required_if:message_type,template|nullable|numeric|exists:templates,id',
            'meta' => 'nullable',
            'status' => 'required|in:active,inactive',
        ];

        $validation_message = [
            'keywords.required' => 'The keywords field is required',
            'platform_id.required' => 'The device field is required',
            'message.required' => 'The message field is required',
            'template_id.required' => 'The Template is required',
        ];


        $validated = TemplateValidation::validate($request, $validation_params, $validation_message);

        $autoReply->update($validated);

        return to_route('user.whatsapp.auto-replies.index')
            ->with('success', 'Updated successfully');
    }

    public function destroy(AutoReply $autoReply)
    {
        $autoReply->delete();
        return back()->with('success', 'Deleted successfully');
    }
}
