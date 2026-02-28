<?php

namespace App\Http\Controllers\User;

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
        PageHeader::set('Activity Logs');

        $activityLogs = ActivityLog::query()
            ->with([
                'user:id,name,email',
                'creator:id,name,email',
                'workspace:id,name',
            ])
            ->where('user_id', auth()->id())
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

        return Inertia::render('User/ActivityLogs/Index', compact('activityLogs'));
    }

    public function show($id)
    {
        PageHeader::set(
            title: 'Activity Logs',
            buttons: [
                [
                    'text' => 'Back',
                    'url' => route('user.activity-logs.index'),
                ]
            ]
        );

        $activityLog = ActivityLog::where('user_id', auth()->id())->findOrFail($id);
        $activityLog->load([
            'user:id,name,email',
            'creator:id,name,email',
            'workspace:id,name',
        ]);
        return Inertia::render('User/ActivityLogs/Show', compact('activityLog'));
    }
}
