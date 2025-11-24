<?php

namespace App\Providers;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Policies\ItemPolicy;
use App\Policies\StorePolicy;
use App\Policies\TransactionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Gate::policy(Store::class, StorePolicy::class);
        Gate::policy(Item::class, ItemPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Explicit route model binding for OrganizationUnit scoped to Organization
        Route::bind('unit', function ($value) {
            $route = request()->route();
            $organization = $route ? $route->parameter('organization') : null;

            // Handle both resolved Organization model and string ID
            if ($organization instanceof Organization) {
                $organizationId = $organization->id;
            } elseif (is_numeric($organization)) {
                $organizationId = (int) $organization;
            } else {
                return null;
            }

            return OrganizationUnit::where('id', $value)
                ->where('organization_id', $organizationId)
                ->firstOrFail();
        });
    }
}
