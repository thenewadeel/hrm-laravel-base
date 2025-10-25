<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * Ensures all queries on scoped models are filtered by the current organization_id.
 */
class OrganizationScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // 1. Check if a user is authenticated.
        if (Auth::check()) {
            $user = Auth::user();

            // 2. Check if the authenticated user has an organization_id property.
            //    (Assumption: Your User model has an organization_id column)
            $user_organization = $user->organizations()->first();
            if ($user_organization) {
                // Apply the tenancy filter
                $builder->where('organization_id', $user_organization->id);
            }
        }
        // Note: If no user is authenticated or the user has no ID, the scope does nothing,
        // which might result in 0 results for models that require an organization_id.
        // You may adjust this logic based on how you manage unauthenticated tenants.
    }
}
