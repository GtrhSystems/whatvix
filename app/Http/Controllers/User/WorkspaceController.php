<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Inertia\Inertia;
use App\Helpers\Toastr;
use App\Models\Workspace;
use App\Helpers\PageHeader;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\StoreWorkspaceRequest;
use App\Http\Requests\UpdateWorkspaceRequest;

class WorkspaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        PageHeader::set('My Workspaces')
            ->addLink(
                'Create Workspace',
                url: route('user.workspaces.create'),
                icon: 'bx:plus'
            )->addOverview('My Workspaces', $this->authUser->myWorkspaces()->count(), 'bx:grid-alt')
            ->addOverview('Shared Workspaces', $this->authUser->assignedWorkspaces()->count(), 'bx:group')
            ->addOverview('My Teams', $this->authUser->teamMembers()->count(), 'bx:user');

        $myWorkspaceIds = $this->authUser->myWorkspaces()->pluck('workspaces.id')->toArray();
        $assignedWorkspaceIds = $this->authUser->assignedWorkspaces()->pluck('workspaces.id')->toArray();
        $allWorkspaceIds = array_merge($myWorkspaceIds, $assignedWorkspaceIds);

        $workspaces = Workspace::whereIn('id', $allWorkspaceIds)
            ->filterOn(['name'])
            ->withCount('members')
            ->paginate()
            ->through(function ($workspace) {
                $workspace->is_owner = $workspace->owner_id === $this->authUser->id;
                return $workspace;
            });

        return Inertia::render('User/Workspaces/Index', compact('workspaces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        validateUserPlan('workspaces');
        PageHeader::set('Create Workspace');

        $modules = getActiveModules();
        return Inertia::render('User/Workspaces/Create', compact('modules'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWorkspaceRequest $request)
    {
        validateUserPlan('workspaces');

        DB::beginTransaction();
        $workspace = $this->authUser->myWorkspaces()->create($request->validated());

        if ($this->authUser->allWorkspaces()->count() > 0 && $this->authUser->current_workspace_id == null) {
            $this->setUserActiveWorkspace($workspace->id);
        }
        DB::commit();

        return redirect()->route('user.workspaces.index')->with('success', __('Workspace created successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Workspace $workspace)
    {
        $workspace->validateOwnership();

        PageHeader::set('Workspace Details')
            ->addModal('Assign Members', 'assignToWorkspace')
            ->addBackLink(route('user.workspaces.index'));

        /** @var \App\Models\User **/
        $user = Auth::user();

        $members = $workspace->members()->paginate();
        $allMembers = $user->teamMembers()->get();

        return Inertia::render('User/Workspaces/Show', compact('workspace', 'members', 'allMembers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Workspace $workspace)
    {
        $workspace->validateOwnership();
        abort_unless($workspace->owner_id === Auth::id(), 403, 'You do not have permission to edit this workspace.');

        PageHeader::set('Edit Workspace');

        $modules = getActiveModules();
        return Inertia::render('User/Workspaces/Create', compact('modules', 'workspace'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWorkspaceRequest $request, Workspace $workspace)
    {
        abort_unless($workspace->owner_id === Auth::id(), 403, 'You do not have permission to edit this workspace.');

        if ($request->has('member_ids')) {
            $workspace->members()->sync($request->member_ids);
            return to_route('user.workspaces.index')
                ->with('success', __('Workspace members updated successfully'));
        }

        $workspace->update($request->validated());

        return to_route('user.workspaces.index')
            ->with('success', __('Workspace updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Workspace $workspace)
    {
        abort_unless($workspace->owner_id === Auth::id(), 403, 'You do not have permission to edit this workspace.');
        $workspace->delete();
        $this->forgetUserMenuCache();
        Toastr::success(__('Workspace deleted successfully'));
        return inertia()->location(route('user.workspaces.index'));
    }

    public function switch($id)
    {
        $this->setUserActiveWorkspace($id);
        Toastr::success(__('Workspace switched successfully'));
        return inertia()->location(route('user.dashboard'));
    }

    /**
     * Set the currently active workspace for the authenticated user.
     */
    protected function setUserActiveWorkspace(int $id): void
    {
        $workspace = $this->authUser->allWorkspaces()->where('id', $id)->firstOrFail();
        $this->authUser->setCurrentWorkspace($workspace->id);
        $this->forgetUserMenuCache();
    }

    public function removeMember(Workspace $workspace, User $member)
    {
        abort_unless($workspace->owner_id === Auth::id(), 403, 'You do not have permission to edit this workspace.');
        // check the member is in the workspace
        if ($workspace->members()->where('users.id', $member->id)->count() == 0) {
            return back()->with('danger', __('Member not found'));
        }

        $workspace->members()->detach($member->id);

        return back()->with('success', __('Member removed successfully'));
    }

    public function leave(Workspace $workspace)
    {
        $isValidMember = $workspace->members()->find($this->authUser->id);

        if (!$isValidMember) {
            return back()->with('danger', __('You are not a member of this workspace'));
        }

        $workspace->members()->detach($this->authUser->id);

        $this->forgetUserMenuCache();

        Toastr::success(__('You have left the workspace successfully'));

        return Inertia::location(route('user.dashboard'));
    }

    private function forgetUserMenuCache()
    {
        Cache::forget("menu_sidebar_user_menu_{$this->authUser->id}");
    }
}
