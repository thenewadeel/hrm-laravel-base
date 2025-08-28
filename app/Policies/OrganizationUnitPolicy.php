<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizationUnitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrganizationUnit $organizationUnit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Organization $organization)
    {
        return $organization->users()
            ->where('user_id', $user->id)
            ->whereJsonContains('roles', 'admin')
            ->exists();
    }


    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrganizationUnit $organizationUnit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrganizationUnit $organizationUnit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrganizationUnit $organizationUnit): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrganizationUnit $organizationUnit): bool
    {
        return false;
    }

    public function assign(User $user, OrganizationUnit $unit): bool
    {
        // Only org admins can assign users to units
        return $this->isAdmin($user, $unit->organization);
    }
    // ---------------------------
    // Helper Methods
    // ---------------------------

    protected function isAdmin(User $user, Organization $organization): bool
    {
        return $this->hasAnyRole($user, $organization, ['admin']);
    }

    protected function hasAnyRole(
        User $user,
        Organization $organization,
        array $roles
    ): bool {
        return $organization->users()
            ->where('user_id', $user->id)
            ->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhereJsonContains('roles', $role);
                }
            })
            ->exists();
    }
}
