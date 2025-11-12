<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Permissions\OrganizationPermissions;
use Illuminate\Auth\Access\Response;

class OrganizationUnitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(OrganizationPermissions::VIEW_ORGANIZATION_UNITS);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrganizationUnit $unit): bool
    {
        return $user->hasPermission(OrganizationPermissions::VIEW_ORGANIZATION_UNITS) &&
            $user->organizations->contains($unit->organization_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Organization $organization)
    {
        return $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION_UNITS);
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrganizationUnit $unit): bool
    {
        return $user->hasPermission(OrganizationPermissions::EDIT_ORGANIZATION_UNITS) &&
            $user->organizations->contains($unit->organization_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrganizationUnit $unit): bool
    {
        return $user->hasPermission(OrganizationPermissions::DELETE_ORGANIZATION_UNITS) &&
            $user->organizations->contains($unit->organization_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrganizationUnit $unit): bool
    {
        return $user->hasPermission(OrganizationPermissions::EDIT_ORGANIZATION_UNITS) &&
            $user->organizations->contains($unit->organization_id);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrganizationUnit $unit): bool
    {
        return $user->hasPermission(OrganizationPermissions::DELETE_ORGANIZATION_UNITS) &&
            $user->organizations->contains($unit->organization_id);
    }

    public function assign(User $user, OrganizationUnit $unit): bool
    {
        return $user->hasPermission(OrganizationPermissions::ASSIGN_ORGANIZATION_USERS || OrganizationPermissions::CREATE_ORGANIZATION_USERS) &&
            $user->organizations->contains($unit->organization_id);
    }
}
