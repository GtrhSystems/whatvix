<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\QuickReply;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuickReplyRequest;

class QuickReplyController extends Controller
{
    public function index(Request $request)
    {



        $query = QuickReply::query();

        PageHeader::set()
            ->title('Quick Replies')
            ->overviews([
                [
                    'icon' => "bx-list-ul",
                    'style' => 'bg-primary-500 text-primary-500',
                    'value' => $query->clone()->count(),
                    'title' => 'Total Quick Replies'
                ],
                [
                    'icon' => "bx-checkbox-checked",
                    'style' => 'bg-success-500 text-success-500',
                    'value' => $query->clone()->active()->count(),
                    'title' => 'Active Quick Replies'
                ],
                [
                    'icon' => "bx-x-circle",
                    'style' => 'bg-danger-500 text-danger-500',
                    'value' => $query->clone()->inactive()->count(),
                    'title' => 'Inactive Quick Replies'
                ],
            ]);

        $quickReplies = $query->with('owner:id,name')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('text', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate();

        return Inertia::render("Admin/Logs/QuickReplies/Index", compact('quickReplies'));
    }

    public function destroy(QuickReply $quickReply)
    {
        $quickReply->delete();
        return to_route("admin.logs.quick-replies.index")->with('success', __('Quick Replies Deleted Successfully'));
    }
}
