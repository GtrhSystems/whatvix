<?php

namespace App\Http\Controllers\Admin\Logs;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkspaceInvitationMail;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = User::query();

        PageHeader::set(
            title: 'Audiences',
            buttons: [
                [
                    'text' => __('Create Member'),
                    'url' => route('admin.logs.members.create'),
                ],
            ],
            overviews: [
                [
                    'icon' => "bx:list-ul",
                    'title' => 'Total Members',
                    'value' => $query->clone()->count(),
                ],
                [
                    'icon' => "bx:calendar",
                    'title' => 'Last 7 Days Members',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(7), now()])->count(),
                ],
                [
                    'icon' => "bx:calendar",
                    'title' => 'Last 30 Days Members',
                    'value' => $query->clone()->whereBetween('created_at', [now()->subDays(30), now()])->count(),
                ],
            ]
        );

        $members = $query->clone()
            ->when(request('workspace_id'), function ($query) {
                $query->whereHas('assignedWorkspaces', function ($query) {
                    $query->where('workspaces.id', request('workspace_id'));
                });
            })
            ->whereRole('user')
            ->with(['assignedWorkspaces'])
            ->paginate()->through(function ($member) {
                $member->invited_by = $member->getReferUser(['id', 'name', 'email', 'avatar']);
                return $member;
            });

        return Inertia::render('Admin/Logs/Members/Index', compact(
            'members',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        PageHeader::set(
            title: 'Add Member',
            buttons: [
                [
                    'text' => __('Back'),
                    'url' => route('admin.logs.members.index'),
                ]
            ]
        );

        $owners = User::query()
            ->with('myWorkspaces:id,owner_id,name,modules')
            ->where('role', 'user')
            ->get();

        return Inertia::render('Admin/Logs/Members/Create', compact('owners'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'owner_id' => ['required', 'exists:users,id'],
            'workspace_ids' => ['required', 'array'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:120', 'email', 'unique:users'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $validated['role'] = 'user';
        $owner = User::find($validated['owner_id']);

        DB::beginTransaction();

        /** @var \App\Models\User **/
        $member = User::create($validated);
        // add the user to the team
        $owner->teamMembers()->attach($member->id);


        $workspaces = $owner->myWorkspaces()->whereIn('id', $request->workspace_ids)->get();

        // add the user to the workspaces   
        foreach ($workspaces as $workspace) {
            $workspace->members()->attach($member->id);
        }


        DB::commit();

        try {
            Mail::to($member)->send((new WorkspaceInvitationMail($member))->afterCommit());
        } catch (\Exception $e) {
            Toastr::info('Failed to send invitation email to ' . $member->email . ': ' . $e->getMessage());
        }

        return to_route('admin.logs.members.index')
            ->with('success', 'Member Added Successfully');
    }

    public function edit(User $member)
    {
        PageHeader::set(
            title: 'Edit Member',
            buttons: [
                [
                    'text' => __('Back'),
                    'url' => route('admin.logs.members.index'),
                ]
            ]
        );

        $owner = $member->load('myWorkspaces:id,owner_id,name,modules');

        return Inertia::render('Admin/Logs/Members/Create', compact('member', 'owner'));
    }

    public function update(Request $request, User $member)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:120', 'email', 'unique:users,id,' . $member->id],
            'password' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['role'] = 'user';

        $member->update($validated);
        return to_route('admin.logs.members.index')->with('success', 'Member Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $member)
    {
        $member->assignedWorkspaces()->detach();

        $member->delete();

        return back()->with('success', 'Member Removed Successfully');
    }
}
