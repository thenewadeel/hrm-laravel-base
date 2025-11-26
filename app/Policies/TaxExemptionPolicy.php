<?php

namespace App\Policies;

use App\Models\Accounting\TaxExemption;
use App\Models\User;

class TaxExemptionPolicy
{
    /**
     * Determine whether user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage accounting');
    }

    /**
     * Determine whether user can view model.
     */
    public function view(User $user, TaxExemption $taxExemption): bool
    {
        return $user->can('manage accounting') &&
               $taxExemption->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage accounting');
    }

    /**
     * Determine whether user can update model.
     */
    public function update(User $user, TaxExemption $taxExemption): bool
    {
        return $user->can('manage accounting') &&
               $taxExemption->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can delete model.
     */
    public function delete(User $user, TaxExemption $taxExemption): bool
    {
        return $user->can('manage accounting') &&
               $taxExemption->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can restore model.
     */
    public function restore(User $user, TaxExemption $taxExemption): bool
    {
        return $user->can('manage accounting') &&
               $taxExemption->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can permanently delete model.
     */
    public function forceDelete(User $user, TaxExemption $taxExemption): bool
    {
        return $user->can('manage accounting') &&
               $taxExemption->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TaxExemption $taxExemption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TaxExemption $taxExemption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaxExemption $taxExemption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaxExemption $taxExemption): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TaxExemption $taxExemption): bool
    {
        return false;
    }
}
