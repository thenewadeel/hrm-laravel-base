<?php

namespace App\Providers;

use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\User;
use App\Permissions\InventoryPermissions;
use App\Roles\InventoryRoles;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Inventory Permission Gates - organization context aware
        Gate::define(InventoryPermissions::VIEW_STORES, function (User $user, $organization = null) {
            return $user->hasPermission(InventoryPermissions::VIEW_STORES, $organization);
        });

        Gate::define(InventoryPermissions::CREATE_STORES, function (User $user) {
            return $user->hasPermission(InventoryPermissions::CREATE_STORES);
        });

        Gate::define(InventoryPermissions::EDIT_STORES, function (User $user, Store $store) {
            return $user->hasPermission(InventoryPermissions::EDIT_STORES, $store->organization);
        });

        // Add similar gates for other permissions...

        // Role-based gates with organization context
        Gate::define('inventory.admin', function (User $user, $organization = null) {
            return $user->hasRole(InventoryRoles::INVENTORY_ADMIN, $organization);
        });

        Gate::define('inventory.manager', function (User $user, $organization = null) {
            return $user->hasRole(InventoryRoles::STORE_MANAGER, $organization) ||
                $user->hasRole(InventoryRoles::INVENTORY_ADMIN, $organization);
        });

        Gate::define('inventory.clerk', function (User $user, $organization = null) {
            return $user->hasRole(InventoryRoles::INVENTORY_CLERK, $organization) ||
                $user->hasRole(InventoryRoles::STORE_MANAGER, $organization) ||
                $user->hasRole(InventoryRoles::INVENTORY_ADMIN, $organization);
        });
    }
}
