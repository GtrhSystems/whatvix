<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasWorkspace
{

    /**
     * Get the currently active workspace.
     *
     * @return int|null
     */
    public function getCurrentWorkspace(): ?Workspace
    {
        return $this->activeWorkspace()->first();
    }

    /**
     * The currently active workspace that the user is in.
     *
     * @return BelongsTo
     */
    public function activeWorkspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'current_workspace_id');
    }

    /**
     * Set the user's current workspace by its ID.
     *
     * @param int $workspaceId The ID of the workspace to set as current.
     * @return bool True if the workspace was successfully set, false otherwise.
     */
    public function setCurrentWorkspace(int $workspaceId): bool
    {
        $this->current_workspace_id = $workspaceId;
        return $this->save();
    }

    /**
     * Get the owner id of the currently active workspace.
     *
     * @return int|null
     */
    public function getActiveWorkspaceOwnerId(): ?int
    {
        return Workspace::find($this->current_workspace_id)?->owner_id;
    }

    /**
     * Retrieve all workspaces owned by the user.
     *
     * This function returns a collection of workspaces where the user is the owner.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function myWorkspaces()
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    public function assignedWorkspaces()
    {
        return $this->belongsToMany(
            Workspace::class,
            'workspace_users',
            'user_id',
            'workspace_id'
        );
    }

    public function allWorkspaces(): Collection
    {
        $assignedWorkspaces = $this->assignedWorkspaces()->get();
        return $this->myWorkspaces()->get()->merge($assignedWorkspaces);
    }

    /**
     * Get all team members associated with the user.
     *
     * This function returns a collection of users who are members of the user's team.
     * The team is determined by the user_team_members pivot table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teamMembers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_team_members',
            'user_id',
            'member_id'
        );
    }

    /**
     * Check if the user has permission for a specific module.
     *
     * @param string $module The module to check for permission.
     * @param bool $abort Whether to abort with a 403 error if permission is not granted. Default is false.
     * @param string|null $message The error message to display if permission is not granted. Default is 'You do not have permission to access this page'.
     * @return bool Returns true if the user has permission for the module, false otherwise.
     */
    public function hasModulePermissionTo(string $module, bool $abort = false, ?string $message = null): bool
    {
        if (in_array($this->role, ['admin', 'user'])) {
            return true;
        }

        $result = $this->allWorkspaces()
            ->where('modules', 'like', '%' . $module . '%')
            ->count();

        if (!$result && $abort) {
            abort(403, $message ?? 'You do not have permission to access this page');
        }

        return $result;
    }
}
