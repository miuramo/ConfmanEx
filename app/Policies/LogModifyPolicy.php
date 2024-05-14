<?php

namespace App\Policies;

use App\Models\LogModify;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class LogModifyPolicy
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
    public function view(User $user, LogModify $logModify): bool
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
    public function update(User $user, LogModify $logModify): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LogModify $logModify): bool
    {
        return Gate::allows('admin', $user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LogModify $logModify): bool
    {
        return Gate::allows('admin', $user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LogModify $logModify): bool
    {
        return Gate::allows('admin', $user);
    }
}
