<?php

namespace App\Policies\Membership;

use App\Models\Membership\MemberFee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FeePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('membership.view_fees');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MemberFee $memberFee): bool
    {
        return $user->hasPermissionTo('membership.view_fees') 
            && $memberFee->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('membership.manage_fees');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MemberFee $memberFee): bool
    {
        return $user->hasPermissionTo('membership.manage_fees') 
            && $memberFee->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MemberFee $memberFee): bool
    {
        return $user->hasPermissionTo('membership.manage_fees') 
            && $memberFee->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MemberFee $memberFee): bool
    {
        return $user->hasPermissionTo('membership.manage_fees') 
            && $memberFee->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MemberFee $memberFee): bool
    {
        return $user->hasPermissionTo('membership.manage_fees') 
            && $memberFee->organization_id === $user->current_organization_id;
    }
}
