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
     * @return void
     */
    // In App\Models\Scopes\OrganizationScope.php

    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // ✨ Use the centralized method
            $orgId = $user->operatingOrganizationId;

            if ($orgId) {
                $builder->where('organization_id', $orgId);
            }
        }
    }
}
