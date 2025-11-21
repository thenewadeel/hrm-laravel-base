<?php

namespace App\Policies;

use App\Models\Accounting\TaxRate;
use App\Models\User;

class TaxRatePolicy
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
    public function view(User $user, TaxRate $taxRate): bool
    {
        return $user->can('manage accounting') &&
               $taxRate->organization_id === $user->current_organization_id;
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
    public function update(User $user, TaxRate $taxRate): bool
    {
        return $user->can('manage accounting') &&
               $taxRate->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can delete model.
     */
    public function delete(User $user, TaxRate $taxRate): bool
    {
        return $user->can('manage accounting') &&
               $taxRate->organization_id === $user->current_organization_id &&
               ! $taxRate->calculations()->exists();
    }

    /**
     * Determine whether user can restore model.
     */
    public function restore(User $user, TaxRate $taxRate): bool
    {
        return $user->can('manage accounting') &&
               $taxRate->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can permanently delete model.
     */
    public function forceDelete(User $user, TaxRate $taxRate): bool
    {
        return $user->can('manage accounting') &&
               $taxRate->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TaxRate $taxRate): bool
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
    public function update(User $user, TaxRate $taxRate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaxRate $taxRate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaxRate $taxRate): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TaxRate $taxRate): bool
    {
        return false;
    }
}
