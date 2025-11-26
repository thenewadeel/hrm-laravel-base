<?php

namespace App\Policies;

use App\Models\Accounting\TaxFiling;
use App\Models\User;

class TaxFilingPolicy
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
    public function view(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id;
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
    public function update(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can delete model.
     */
    public function delete(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id &&
               ! in_array($taxFiling->status, ['accepted', 'paid']);
    }

    /**
     * Determine whether user can restore model.
     */
    public function restore(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can permanently delete model.
     */
    public function forceDelete(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id;
    }

    /**
     * Determine whether user can approve filing.
     */
    public function approve(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id &&
               $taxFiling->status === 'draft';
    }

    /**
     * Determine whether user can mark filing as paid.
     */
    public function markAsPaid(User $user, TaxFiling $taxFiling): bool
    {
        return $user->can('manage accounting') &&
               $taxFiling->organization_id === $user->current_organization_id &&
               in_array($taxFiling->status, ['filed', 'accepted']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TaxFiling $taxFiling): bool
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
    public function update(User $user, TaxFiling $taxFiling): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TaxFiling $taxFiling): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TaxFiling $taxFiling): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TaxFiling $taxFiling): bool
    {
        return false;
    }
}
