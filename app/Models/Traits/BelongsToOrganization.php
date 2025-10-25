<?php

namespace App\Models\Traits;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;

/**
 * Trait to apply the OrganizationScope globally and define the relationship.
 */
trait BelongsToOrganization
{
    /**
     * Boot the trait and apply the global scope.
     * Laravel automatically detects and calls a method named boot[TraitName].
     *
     * @return void
     */
    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope(new OrganizationScope);

        // Optional: Ensure the organization_id is automatically filled on creation
        // This is a common practice to prevent tenancy leaks.
        static::creating(function ($model) {
            $user = auth()->user();
            $user_organization = $user->organizations()->first();
            if ($user && $user_organization) {
                $model->organization_id = $user_organization->id;
            }
        });
    }

    /**
     * Define the inverse one-to-many relationship with the Organization model.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
