<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\Workspace;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkspaceController extends Controller
{
    public function index()
    {

        $query = Workspace::query();
        PageHeader::set(
            title: 'Workspaces | Logs',
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'title' => 'Total Workspaces',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:history",
                    'title' => 'Last 7 Days Workspaces',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                ],
                [
                    'icon' => "bx:history",
                    'title' => 'Last 30 Days Workspaces',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                ],

            ]
        );

        $workspaces = $query
            ->with('owner:id,name')
            ->withCount(['members'])
            ->paginate();

        return Inertia::render('Admin/Logs/Workspaces/Index', compact('workspaces'));
    }

    public function destroy(Workspace $workspace)
    {
        $workspace->delete();
        return to_route('admin.logs.workspaces.index')
            ->with('success', __('Workspace deleted successfully'));
    }

}
