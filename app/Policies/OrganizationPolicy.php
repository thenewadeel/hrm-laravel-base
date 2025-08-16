<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    /**
     * Determine whether the user can view any organizations.
     */
    public function viewAny(User $user): bool
    {
        // Typically, users can view organizations they belong to
        return true;
    }

    /**
     * Determine whether the user can view a specific organization.
     */
    public function view(User $user, Organization $organization): bool
    {
        // User can view if they're a member of the organization
        return $organization->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create organizations.
     */
    public function create(User $user): bool
    {
        // Typically any authenticated user can create an organization
        return true;
    }

    /**
     * Determine whether the user can update the organization.
     */
    public function update(User $user, Organization $organization): bool
    {
        // Only organization owners/admins can update
        return $organization->users()
            ->where('user_id', $user->id)
            ->whereJsonContains('roles', 'admin')
            ->exists();
    }

    /**
     * Determine whether the user can delete the organization.
     */
    public function delete(User $user, Organization $organization): bool
    {
        // Only organization owners can delete
        return $organization->owner_id === $user->id;
    }

    /**
     * Custom method for viewing members
     */
    public function viewMembers(User $user, Organization $organization)
    {
        // Database-agnostic way to check roles
        return $organization->users()
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->whereJsonContains('roles', 'admin')
                    ->orWhereJsonContains('roles', 'moderator');
            })
            ->exists();
    }



    /**
     * Determine whether the user can invite new members
     */
    public function inviteMembers(User $user, Organization $organization)
    {
        // Only organization admins can invite members
        return   $organization->users()
            ->where('user_id', $user->id)
            // ->where('roles', 'like', '%"admin"%')
            ->where(function ($query) {
                $query->whereJsonContains('roles', 'admin');
                // ->orWhereJsonContains('roles', 'moderator');
            })->exists();
    }
}
