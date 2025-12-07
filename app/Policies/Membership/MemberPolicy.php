<?php

namespace App\Policies\Membership;

use App\Models\Membership\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('membership.view_members');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Member $member): bool
    {
        return $user->hasPermission('membership.view_members')
            && $member->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('membership.create_members');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Member $member): bool
    {
        return $user->hasPermission('membership.edit_members')
            && $member->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Member $member): bool
    {
        return $user->hasPermission('membership.delete_members')
            && $member->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Member $member): bool
    {
        return $user->hasPermission('membership.edit_members')
            && $member->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Member $member): bool
    {
        return $user->hasPermission('membership.delete_members')
            && $member->organization_id === $user->current_organization_id;
    }
}
