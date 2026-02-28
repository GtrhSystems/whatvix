<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\Group;
use App\Helpers\PageHeader;
use App\Http\Controllers\Controller;

class GroupController extends Controller
{
    public function index()
    {
        $query = Group::query();

        PageHeader::set(
            title: 'Groups',
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'value' => $query->clone()->count(),
                    'title' => 'Total Audiences Group'
                ],
                [
                    'icon' => "bx:history",
                    'title' => 'Last 7 Days Audiences Group',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                ],
                [
                    'icon' => "bx:calendar",
                    'title' => 'Last 30 Days Audiences Group',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                ],
            ]
        );

        $groups = $query->withCount('customers')->with('owner:id,name')->latest()->paginate();

        return Inertia::render('Admin/Logs/Groups/Index', compact('groups', ));
    }

    public function destroy(Group $group)
    {
        $group->delete();
        return back()->with('success', __('Group Deleted Successfully'));
    }
}
