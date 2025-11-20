<?php

namespace App\Providers;

use App\Models\Inventory\Store;
use App\Models\JobPosition;
use App\Models\Shift;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use App\Permissions\InventoryPermissions;
use App\Roles\InventoryRoles;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        JobPosition::class => \App\Policies\JobPositionPolicy::class,
        Shift::class => \App\Policies\ShiftPolicy::class,
    ];

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

        // Report permissions gate - ADD THIS
        Gate::define('inventory.reports.view', function (User $user) {
            return $user->hasPermission('inventory.reports.view');
        });

        // Accounting Permission Gates
        Gate::define(AccountingPermissions::CREATE_CASH_RECEIPTS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::CREATE_CASH_RECEIPTS);
        });

        Gate::define(AccountingPermissions::CREATE_CASH_PAYMENTS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::CREATE_CASH_PAYMENTS);
        });

        Gate::define(AccountingPermissions::VIEW_CASH_RECEIPTS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::VIEW_CASH_RECEIPTS);
        });

        Gate::define(AccountingPermissions::VIEW_CASH_PAYMENTS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::VIEW_CASH_PAYMENTS);
        });

        Gate::define(AccountingPermissions::VIEW_CASH_REPORTS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::VIEW_CASH_REPORTS);
        });

        Gate::define(AccountingPermissions::GENERATE_CASH_REPORTS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::GENERATE_CASH_REPORTS);
        });

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
