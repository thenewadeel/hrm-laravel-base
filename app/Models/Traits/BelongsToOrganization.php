<?php

namespace App\Models\Traits;

use App\Models\Organization;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Support\Facades\Auth;

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
    // In App\Models\Traits\BelongsToOrganization.php

    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope(new OrganizationScope);

        static::creating(function ($model) {
            // Don't override organization_id if it's already set
            if (! empty($model->organization_id)) {
                return;
            }

            $user = Auth::user();

            if ($user) {
                $orgId = null;

                if ($user->current_organization_id) {
                    $orgId = $user->current_organization_id;
                } elseif ($user->organizations->isNotEmpty()) { // ✅ Safe access
                    $orgId = $user->organizations->first()->id;
                }

                if ($orgId) {
                    $model->organization_id = $orgId;

                    return; // Stop here if organization_id is set
                }
            }

            // Fallback for Tests/Console/No Auth
            if (empty($model->organization_id)) {
                $firstOrganization = Organization::query()->first(['id']);

                if ($firstOrganization) {
                    $model->organization_id = $firstOrganization->id;
                }
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

    public function scopeOfOrganization($query, string $organization)
    {
        return $query->where('organization_id', $organization);
    }
}
