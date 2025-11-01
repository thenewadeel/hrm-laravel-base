<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use App\Permissions\OrganizationPermissions;
use App\Roles\InventoryRoles;
use App\Roles\OrganizationRoles;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        // Typically true - users can see organizations they belong to via scopes
        return true;
    }

    public function view(User $user, Organization $organization): bool
    {
        return
            $user->organizations->contains($organization->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return true; //$user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION) || $user->hasRole(OrganizationRoles::SUPER_ADMIN);
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organization $organization): bool
    {
        return $user->hasPermission(OrganizationPermissions::EDIT_ORGANIZATION) &&
            $user->organizations->contains($organization->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $user->hasPermission(OrganizationPermissions::DELETE_ORGANIZATION) &&
            $user->organizations->contains($organization->id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organization $organization): bool
    {
        return $user->hasPermission(OrganizationPermissions::EDIT_ORGANIZATION) &&
            $user->organizations->contains($organization->id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organization $organization): bool
    {
        return false; // Short circuit
        return $user->hasPermission(OrganizationPermissions::DELETE_ORGANIZATION) &&
            $user->organizations->contains($organization->id);
    }

    public function viewMembers(User $user, Organization $organization)
    {
        return $user->hasPermission(OrganizationPermissions::VIEW_ORGANIZATION_USERS || OrganizationPermissions::VIEW_USERS) &&
            $user->organizations->contains($organization->id);
    }

    public function inviteMembers(User $user, Organization $organization): bool
    {
        return $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION_USERS || OrganizationPermissions::CREATE_USERS) &&
            $user->organizations->contains($organization->id);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $user->hasPermission(OrganizationPermissions::EDIT_ORGANIZATION_USERS || OrganizationPermissions::EDIT_USERS) &&
            $user->organizations->contains($organization->id);
    }

    // public function assign(User $user, Organization $organization): bool
    // {
    //     return $user->hasPermission(OrganizationPermissions::ASSIGN_ORGANIZATION_USERS) &&
    //         $user->organizations->contains($organization->id);
    // }
}
