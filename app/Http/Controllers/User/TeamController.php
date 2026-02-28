<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Helpers\PageHeader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkspaceInvitationMail;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set('My Teams')
            ->addLink(
                'Create Member',
                route('user.teams.create'),
                icon: 'bx:plus'
            )->addOverview('Total Members', $this->authUser->teamMembers()->count(), 'bx:group')
            ->addOverview('Last 30d Team Members', $this->authUser->teamMembers()
                ->whereBetween('created_at', [now()->subDays(30), now()])->count(), 'bx:envelope')
            ->addOverview('Total Workspaces', $this->authUser->myWorkspaces()->count(), 'bx:list-ul');
        // get all my workspaces
        $myWorkspaces = $this->authUser->myWorkspaces;

        // get all my team members
        $myTeamMembers = $this->authUser->teamMembers()->with('assignedWorkspaces')->paginate();

        return Inertia::render('User/Teams/Index', compact(
            'myWorkspaces',
            'myTeamMembers',
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        validateUserPlan('team_members');

        PageHeader::set('Add Member')->addBackLink(route('user.teams.index'));

        $workspaces = $this->authUser->myWorkspaces;
        return Inertia::render('User/Teams/Create', compact('workspaces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        validateUserPlan('team_members');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:120', 'email'],
            'password' => ['required', 'string', 'max:255'],
            'workspace_ids' => ['required', 'array'],
        ]);

        if ($this->authUser->teamMembers()->where('email', $request->input('email'))->exists()) {
            return back()->with('danger', 'Member already exists. Please try again with another email.');
        }

        DB::transaction(function () use ($request) {
            /** @var \App\Models\User **/
            $teamUser = User::firstOrCreate(
                [
                    'email' => $request->input('email')
                ],
                [
                    'role' => 'user',
                    'name' => $request->input('name'),
                    'password' => $request->input('password'),
                    'meta' => [
                        'invited_by_email' => $this->authUser->email
                    ]
                ]
            );

            if (!$this->authUser->teamMembers()->find($teamUser->id)) {
                $this->authUser->teamMembers()->attach($teamUser->id);
                $teamUser->assignedWorkspaces()->sync($request->workspace_ids);
                try {
                    Mail::to($teamUser)->send((new WorkspaceInvitationMail($teamUser))->afterCommit());
                } catch (\Exception $e) {
                    Toastr::info('Email could not be sent: ' . $e->getMessage());
                }
            }

        });


        return to_route('user.teams.index')->with('success', __('Member added successfully'));
    }

    public function update(Request $request, User $team)
    {
        if ($request->has('workspace_ids')) {
            $team->assignedWorkspaces()->sync($request->input('workspace_ids'));
            Toastr::success('Workspaces updated successfully');
        }
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $team)
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->id == $team->id) {
            return back()->with('danger', 'You cannot remove yourself');
        }

        $isUnderTeam = $user->teamMembers()->where([
            'users.id' => $team->id
        ])->count();

        abort_unless($isUnderTeam, 403, 'You are not allowed to remove this member');

        $team->delete();

        return back()->with('success', 'Member Removed Successfully');
    }
}
