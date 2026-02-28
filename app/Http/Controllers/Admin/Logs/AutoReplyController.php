<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\AutoReply;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AutoReplyController extends Controller
{
    public function index(Request $request)
    {
        PageHeader::set()->title('Auto Replies')->overviews([
            [
                'icon' => "bx-list-ul",
                'title' => 'Total Auto Replies',
                'value' => AutoReply::query()->count(),
            ],
            [
                'title' => 'Last 7 Days Auto Replies',
                'value' => AutoReply::query()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                'icon' => "bx:history",
            ],
            [
                'title' => 'Last 30 Days Auto Replies',
                'value' => AutoReply::query()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                'icon' => "bx:calendar",
            ],
        ]);

        $autoReplies = AutoReply::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('keyword', 'like', "%{$request->search}%")
                    ->orWhereRelation('template', 'name', 'like', '%' . $request->search . '%');
            })
            ->with('platform:id,name', 'template:id,name', 'owner:id,name')
            ->latest()
            ->paginate();

        return Inertia::render(
            'Admin/Logs/AutoReplies/Index',
            compact(
                'autoReplies',
            )
        );
    }

    public function destroy(AutoReply $autoReply)
    {
        $autoReply->delete();
        return back()->with('success', 'Deleted successfully');
    }
}
