<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasOwner
{
    public $ownerOptions = [
        'column' => 'owner_id',
    ];

    /**
     * Define owner options for the model.
     *
     * @param array $options An array of options to merge with the default owner options.
     *                       This allows customization of the owner column and other settings.
     */

    public function defineHasOwner(array $options = [])
    {
        $this->ownerOptions = array_merge($this->ownerOptions, $options);
    }


    /**
     * Return the column name to check for authorization.
     *
     * If $column is provided, use it as the column name. Otherwise, if the
     * ownerOptions has a 'column' key, use that as the column name.
     * Otherwise, default to 'owner_id'.
     *
     * @param string|null $column The column name to check for authorization.
     * @return string The column name to check for authorization.
     */
    private function getAuthorizeColumn(?string $column = null): string
    {
        return $column ?? $this->ownerOptions['column'] ?? 'owner_id';
    }

    /**
     * Register the necessary events for the trait.
     *
     * @return void
     */
    protected static function bootHasOwner()
    {
        static::updating(function ($model) {
            $model->validateOwnership();
        });

        static::deleting(function ($model) {
            $model->validateOwnership();
        });
    }

    /**
     * Get the owner of the model.
     *
     * @return BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, $this->getAuthorizeColumn());
    }

    /**
     * Check if the current user is the owner of the model.
     *
     * @param  string  $attribute the attribute to check. Defaults to 'owner_id'.
     * @return bool
     */
    public function isOwner(?string $column = null): bool
    {
        if (Auth::check()) {
            return $this->getAttribute($this->getAuthorizeColumn($column)) === auth()->id();
        }

        return false;
    }


    /**
     * Check if the current user is the member of the team that owns the model.
     *
     * @param  string  $column the attribute to check. Defaults to 'owner_id'.
     * @return bool
     */
    public function isOwnerTeam(?string $column = null): bool
    {
        if (Auth::check()) {
            return $this->getAttribute($this->getAuthorizeColumn($column)) === activeWorkspaceOwnerId();
        }

        return false;
    }


    /**
     * Check if the current user is a super admin.
     *
     * @return bool True if the current user is a super admin, false otherwise.
     */
    public function isSuperAdmin(): bool
    {
        return Auth::check() && Auth::user()->isSuperAdmin();
    }


    /**
     * Check if the current user is the owner of the model.
     *
     * @param string $attribute the attribute to check. Defaults to 'owner_id'.
     * @return static
     */
    public function authorizeOwner(?string $attribute = null): static
    {
        abort_if(
            Auth::check() &&
                !$this->isOwner($this->getAuthorizeColumn($attribute)) &&
                !$this->isOwnerTeam($this->getAuthorizeColumn($attribute)) &&
                !$this->isSuperAdmin(),
            403,
            __('You are not authorized to perform this action.')
        );
        return $this;
    }

    /**
     * Check if the current user is the owner of the model or a super admin.
     * If not, throw a 403 error.
     *
     * @param string $attribute the attribute to check. Defaults to 'owner_id'.
     * @return static
     */
    public function validateOwnership(?string $attribute = null): static
    {
        return $this->authorizeOwner($attribute);
    }
}
