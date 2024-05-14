<?php

namespace App\Policies;

use App\Models\Paper;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class PaperPolicy
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
    public function view(User $user, Paper $paper): bool
    {
        if ($paper->owner === $user->id) return true;
        return $paper->isCoAuthorEmail($user->email);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // ただし、許可されている期間
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Paper $paper): bool
    {
        return ($paper->owner === $user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Paper $paper): bool
    {
        return ($paper->owner === $user->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Paper $paper): bool
    {
        return Gate::allows('admin', $user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Paper $paper): bool
    {
        return Gate::allows('admin', $user);
    }
}
