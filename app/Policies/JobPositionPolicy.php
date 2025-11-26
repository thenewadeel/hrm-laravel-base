<?php

namespace App\Policies;

use App\Models\JobPosition;
use App\Models\User;

class JobPositionPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobPosition $jobPosition): bool
    {
        return $user->current_organization_id === $jobPosition->organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->current_organization_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobPosition $jobPosition): bool
    {
        return $user->current_organization_id === $jobPosition->organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobPosition $jobPosition): bool
    {
        return $user->current_organization_id === $jobPosition->organization_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobPosition $jobPosition): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobPosition $jobPosition): bool
    {
        return false;
    }
}
