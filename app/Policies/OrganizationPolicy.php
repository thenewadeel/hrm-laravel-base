<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
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
        // User must be a member of the organization
        return $organization->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        // Any authenticated user can create organizations
        return true;
    }

    public function update(User $user, Organization $organization): bool
    {
        // Only admins can update
        return $this->isAdmin($user, $organization);
    }

    public function delete(User $user, Organization $organization): bool
    {
        // Only owner or super admins can delete
        return $organization->owner_id === $user->id || $user->is_super_admin;
    }

    // public function viewMembers(User $user, Organization $organization): bool
    // {
    //     // Admins or moderators can view members
    //     return $this->hasAnyRole($user, $organization, ['admin', 'moderator']);
    // }

    public function viewMembers(User $user, Organization $organization)
    {
        return $organization->users()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function inviteMembers(User $user, Organization $organization): bool
    {
        // Only admins can invite
        return $this->isAdmin($user, $organization);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        // Only admins can manage members
        return $this->isAdmin($user, $organization);
    }

    public function assignUser(User $user, OrganizationUnit $unit): bool
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
