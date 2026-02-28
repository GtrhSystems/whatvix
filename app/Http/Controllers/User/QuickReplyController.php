<?php

namespace App\Http\Controllers\User;

use Inertia\Inertia;
use App\Models\QuickReply;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuickReplyRequest;
use Illuminate\Support\Facades\Auth;

class QuickReplyController extends Controller
{

    public function __construct(
        private $module = null
    ) {
        if (is_null($this->module)) {
            $this->module = getRequestModuleName()->kebab()->toString();
        }
    }

    public function index(Request $request)
    {
        /** @var \App\Models\User */
        $user = activeWorkspaceOwner();
        $quickReplies = $user->quickReplies()
            ->where('module', $this->module)
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('text', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate();

        $query = $user->quickReplies()->where('module', $this->module);

        PageHeader::set()
            ->title('Quick Replies')
            ->buttons([
                [
                    'url' => route("user.{$this->module}.quick-replies.create"),
                    'text' => 'Add new'
                ]
            ])->overviews([
                [
                    'icon' => "bx-list-ul",
                    'value' => $query->clone()->count(),
                    'title' => 'Total Quick Replies'
                ],
                [
                    'icon' => "bx-checkbox-checked",
                    'value' => $query->clone()->active()->count(),
                    'title' => 'Active Quick Replies'
                ],
                [
                    'icon' => "bx-x-circle",
                    'value' => $query->clone()->inactive()->count(),
                    'title' => 'Inactive Quick Replies'
                ],
            ]);

        return Inertia::render("QuickReplies/Index", compact('quickReplies'));
    }

    public function create()
    {
        PageHeader::set()->title('Quick Replies Create')->buttons([
            [
                'url' => route("user.{$this->module}.quick-replies.index"),
                'text' => 'Back'
            ]
        ]);

        validateWorkspacePlan('quick_reply');

        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();

        $platforms = $user->platforms()
            ->where('module', $this->module)
            ->select('id', 'name')
            ->get();

        $shortCodes = ['{name}'];

        return Inertia::render('QuickReplies/Create', compact(
            'platforms',
            'shortCodes',
        ));
    }

    public function store(QuickReplyRequest $request)
    {
        validateWorkspacePlan('quick_reply');
        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();
        $user->quickReplies()->create($request->validated());
        return to_route("user.{$this->module}.quick-replies.index")->with('success', __('Quick Replies Created Successfully'));
    }

    public function edit(QuickReply $quickReply)
    {
        PageHeader::set()->title('Quick Replies Edit')->buttons([
            [
                'url' => route("user.{$this->module}.quick-replies.index"),
                'text' => 'Back'
            ]
        ]);

        $user = activeWorkspaceOwner();
        $platforms = $user->platforms()->module($this->module)->select('id', 'name')->get();

        $shortCodes = ['{name}', '{phone}'];
        return Inertia::render(
            'QuickReplies/Create',
            compact(
                'platforms',
                'quickReply',
                'shortCodes',
            )
        );
    }

    public function update(QuickReplyRequest $request, QuickReply $quickReply)
    {
        $quickReply->update($request->validated());
        return to_route("user.{$this->module}.quick-replies.index")->with('success', __('Quick Replies updated Successfully'));
    }

    public function destroy(QuickReply $quickReply)
    {
        $quickReply->where('owner_id', activeWorkspaceOwnerId())->delete();
        return to_route("user.{$this->module}.quick-replies.index")->with('success', __('Quick Replies Deleted Successfully'));
    }

    public function getQuickReplyTemplateList(Request $request)
    {
        /**
         * @var \App\Models\User
         */
        $user = activeWorkspaceOwner();
        $templates = $user->quickReplies()
            ->where('module', $this->module)
            ->active()
            ->pluck('message_template');

        return response()->json($templates);
    }
}
