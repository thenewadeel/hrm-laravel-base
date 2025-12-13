<?php

namespace App\Providers;

use App\Models\Accounting\TaxExemption;
use App\Models\Accounting\TaxFiling;
use App\Models\Accounting\TaxRate;
use App\Models\Inventory\Store;
use App\Models\JobPosition;
use App\Models\Membership\Member;
use App\Models\Membership\MemberFee;
use App\Models\Membership\MemberSubscription;
use App\Models\Shift;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use App\Permissions\InventoryPermissions;
use App\Permissions\MembershipPermissions;
use App\Roles\InventoryRoles;
use App\Roles\MembershipRoles;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        JobPosition::class => \App\Policies\JobPositionPolicy::class,
        Shift::class => \App\Policies\ShiftPolicy::class,
        TaxRate::class => \App\Policies\TaxRatePolicy::class,
        TaxExemption::class => \App\Policies\TaxExemptionPolicy::class,
        TaxFiling::class => \App\Policies\TaxFilingPolicy::class,
        Member::class => \App\Policies\Membership\MemberPolicy::class,
        MemberFee::class => \App\Policies\Membership\FeePolicy::class,
        MemberSubscription::class => \App\Policies\Membership\MemberSubscriptionPolicy::class,
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

        // Voucher Management Gates
        Gate::define(AccountingPermissions::CREATE_VOUCHERS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::CREATE_VOUCHERS);
        });

        Gate::define(AccountingPermissions::VIEW_VOUCHERS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::VIEW_VOUCHERS);
        });

        Gate::define(AccountingPermissions::EDIT_VOUCHERS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::EDIT_VOUCHERS);
        });

        Gate::define(AccountingPermissions::DELETE_VOUCHERS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::DELETE_VOUCHERS);
        });

        Gate::define(AccountingPermissions::POST_VOUCHERS, function (User $user) {
            return $user->hasPermission(AccountingPermissions::POST_VOUCHERS);
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

        // Tax Management Gates
        Gate::define('manage accounting', function (User $user) {
            return $user->hasPermission(AccountingPermissions::VIEW_VOUCHERS) ||
                   $user->hasPermission(AccountingPermissions::CREATE_VOUCHERS) ||
                   $user->hasPermission(AccountingPermissions::EDIT_VOUCHERS);
        });

        Gate::define('tax.manage', function (User $user) {
            return $user->hasPermission('tax.manage');
        });

        Gate::define('tax.report', function (User $user) {
            return $user->hasPermission('tax.report');
        });

        Gate::define('tax.file', function (User $user) {
            return $user->hasPermission('tax.file');
        });

        // Membership Permission Gates
        Gate::define(MembershipPermissions::VIEW_MEMBERS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::VIEW_MEMBERS);
        });

        Gate::define(MembershipPermissions::CREATE_MEMBERS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::CREATE_MEMBERS);
        });

        Gate::define(MembershipPermissions::EDIT_MEMBERS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::EDIT_MEMBERS);
        });

        Gate::define(MembershipPermissions::DELETE_MEMBERS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::DELETE_MEMBERS);
        });

        Gate::define(MembershipPermissions::MANAGE_MEMBERS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::MANAGE_MEMBERS);
        });

        Gate::define(MembershipPermissions::VIEW_FEES, function (User $user) {
            return $user->hasPermission(MembershipPermissions::VIEW_FEES);
        });

        Gate::define(MembershipPermissions::MANAGE_FEES, function (User $user) {
            return $user->hasPermission(MembershipPermissions::MANAGE_FEES);
        });

        Gate::define(MembershipPermissions::PROCESS_PAYMENTS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::PROCESS_PAYMENTS);
        });

        Gate::define(MembershipPermissions::WAIVE_FEES, function (User $user) {
            return $user->hasPermission(MembershipPermissions::WAIVE_FEES);
        });

        Gate::define(MembershipPermissions::VIEW_SUBSCRIPTIONS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::VIEW_SUBSCRIPTIONS);
        });

        Gate::define(MembershipPermissions::MANAGE_SUBSCRIPTIONS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::MANAGE_SUBSCRIPTIONS);
        });

        Gate::define(MembershipPermissions::RENEW_SUBSCRIPTIONS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::RENEW_SUBSCRIPTIONS);
        });

        Gate::define(MembershipPermissions::CANCEL_SUBSCRIPTIONS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::CANCEL_SUBSCRIPTIONS);
        });

        Gate::define(MembershipPermissions::PRINT_CARDS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::PRINT_CARDS);
        });

        Gate::define(MembershipPermissions::DESIGN_CARDS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::DESIGN_CARDS);
        });

        Gate::define(MembershipPermissions::VIEW_REPORTS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::VIEW_REPORTS);
        });

        Gate::define(MembershipPermissions::VIEW_DASHBOARD, function (User $user) {
            return $user->hasPermission(MembershipPermissions::VIEW_DASHBOARD);
        });

        Gate::define(MembershipPermissions::EXPORT_DATA, function (User $user) {
            return $user->hasPermission(MembershipPermissions::EXPORT_DATA);
        });

        Gate::define(MembershipPermissions::GENERATE_REPORTS, function (User $user) {
            return $user->hasPermission(MembershipPermissions::GENERATE_REPORTS);
        });

        // Membership Role Gates
        Gate::define(MembershipRoles::MEMBERSHIP_ADMIN, function (User $user, $organization = null) {
            return $user->hasRole(MembershipRoles::MEMBERSHIP_ADMIN, $organization);
        });

        Gate::define(MembershipRoles::MEMBERSHIP_MANAGER, function (User $user, $organization = null) {
            return $user->hasRole(MembershipRoles::MEMBERSHIP_MANAGER, $organization) ||
                   $user->hasRole(MembershipRoles::MEMBERSHIP_ADMIN, $organization);
        });

        Gate::define(MembershipRoles::MEMBERSHIP_CLERK, function (User $user, $organization = null) {
            return $user->hasRole(MembershipRoles::MEMBERSHIP_CLERK, $organization) ||
                   $user->hasRole(MembershipRoles::MEMBERSHIP_MANAGER, $organization) ||
                   $user->hasRole(MembershipRoles::MEMBERSHIP_ADMIN, $organization);
        });

        Gate::define(MembershipRoles::MEMBERSHIP_VIEWER, function (User $user, $organization = null) {
            return $user->hasRole(MembershipRoles::MEMBERSHIP_VIEWER, $organization) ||
                   $user->hasRole(MembershipRoles::MEMBERSHIP_CLERK, $organization) ||
                   $user->hasRole(MembershipRoles::MEMBERSHIP_MANAGER, $organization) ||
                   $user->hasRole(MembershipRoles::MEMBERSHIP_ADMIN, $organization);
        });
    }
}
