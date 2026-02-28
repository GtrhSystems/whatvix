<?php

namespace App\Http\Controllers\Admin\Logs;

use Inertia\Inertia;
use App\Models\Campaign;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

class CampaignController extends Controller
{

    public function index(Request $request)
    {

        $query = Campaign::query();
        PageHeader::set(
            title: 'Campaigns',
            overviews: [
                [
                    'icon' => "bx:grid-alt",
                    'title' => 'Total Campaigns',
                    'value' => $query->count(),
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
            ]
        );

        $campaigns = $query->clone()
            ->with(['group:id,name', 'template:id,name', 'owner:id,name'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $query->whereLike('name', "%{$request->search}%")
                    ->orWhereRelation('template', 'name', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate()
            ->through(function ($campaign) {
                $campaign->successRate = $campaign->success_rate;
                return $campaign;
            });

        return Inertia::render('Admin/Logs/Campaigns/Index', [
            'campaigns' => $campaigns,
        ]);
    }


    public function destroy(Campaign $campaign)
    {
        $campaign->delete();
        return back()->with('success', __('Deleted Successfully'));
    }

}
