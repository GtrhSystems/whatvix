<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\PageHeader;
use Inertia\Inertia;
use App\Models\ActivityLog;
use App\Http\Controllers\Controller;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set('Activity Logs')
            ->addOverview(
                __('Total Actions'),
                ActivityLog::count(),
                'bx:grid-alt'
            )
            ->addOverview(
                __('Last 7 Days Actions'),
                ActivityLog::whereDate('created_at', '>=', now()->subDays(7))->count(),
                'bx:history'
            )
            ->addOverview(
                __('Last 30 Days Actions'),
                ActivityLog::whereDate('created_at', '>=', now()->subDays(30))->count(),
                'bx:time'
            )
        ;

        $activityLogs = ActivityLog::query()
            ->with([
                'user:id,name,email',
                'creator:id,name,email',
                'workspace:id,name',
            ])
            ->when(request('search'), function ($query, $search) {
                return match (request('type')) {
                    'description' => $query->where('description', 'like', "%{$search}%"),
                    'creator_email' => $query->whereHas('creator', fn($query) => $query->where('email', 'like', "%{$search}%")),
                    'user_email' => $query->whereHas('user', fn($query) => $query->where('email', 'like', "%{$search}%")),
                    default => $query
                };
            })
            ->latest()
            ->paginate();

        return Inertia::render('Admin/ActivityLogs/Index', compact('activityLogs'));
    }

    public function show(ActivityLog $activityLog)
    {
        PageHeader::set(
            title: 'Activity Logs',
            buttons: [
                [
                    'text' => 'Back',
                    'url' => route('admin.activity-logs.index'),
                ]
            ]
        );
        $activityLog->load([
            'user:id,name,email',
            'creator:id,name,email',
            'workspace:id,name',
        ]);
        return Inertia::render('Admin/ActivityLogs/Show', compact('activityLog'));
    }
}
