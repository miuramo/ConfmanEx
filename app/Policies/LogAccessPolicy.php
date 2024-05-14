<?php

namespace App\Policies;

use App\Models\LogAccess;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LogAccessPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return Gate::allows('admin', $user);

    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LogAccess $logAccess): bool
    {
        return Gate::allows('admin', $user);

    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LogAccess $logAccess): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LogAccess $logAccess): bool
    {
        return Gate::allows('admin', $user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LogAccess $logAccess): bool
    {
        return Gate::allows('admin', $user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LogAccess $logAccess): bool
    {
        return Gate::allows('admin', $user);
    }
}
