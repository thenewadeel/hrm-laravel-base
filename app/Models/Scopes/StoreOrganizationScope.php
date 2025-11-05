<?php
// Create a new scope for Store
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class StoreOrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $orgId = $user->operatingOrganizationId;

            if ($orgId) {
                $builder->whereHas('organization_unit', function ($query) use ($orgId) {
                    $query->where('organization_id', $orgId);
                });
            }
        }
    }
}
