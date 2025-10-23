<?php

namespace App\Roles;

use App\Permissions\InventoryPermissions;

class InventoryRoles
{
    const INVENTORY_ADMIN = 'inventory_admin';
    const STORE_MANAGER = 'store_manager';
    const INVENTORY_CLERK = 'inventory_clerk';
    const AUDITOR = 'auditor';

    public static function permissions(): array
    {
        return [
            self::INVENTORY_ADMIN => [
                // All inventory permissions
                ...InventoryPermissions::all(),
            ],
            self::STORE_MANAGER => [
                // Store and item management
                InventoryPermissions::VIEW_STORES,
                InventoryPermissions::CREATE_STORES,
                InventoryPermissions::EDIT_STORES,
                // InventoryPermissions::DELETE_STORES,
                InventoryPermissions::MANAGE_STORE_INVENTORY,
                InventoryPermissions::VIEW_ITEMS,
                InventoryPermissions::CREATE_ITEMS,
                InventoryPermissions::EDIT_ITEMS,
                InventoryPermissions::DELETE_ITEMS,

                // Transaction management
                InventoryPermissions::VIEW_TRANSACTIONS,
                InventoryPermissions::CREATE_TRANSACTIONS,
                InventoryPermissions::EDIT_TRANSACTIONS,
                InventoryPermissions::FINALIZE_TRANSACTIONS,
                InventoryPermissions::CANCEL_TRANSACTIONS,

                // Reporting
                InventoryPermissions::VIEW_INVENTORY_REPORTS,
            ],
            self::INVENTORY_CLERK => [
                // Basic inventory operations
                InventoryPermissions::VIEW_STORES,
                InventoryPermissions::VIEW_ITEMS,
                InventoryPermissions::VIEW_TRANSACTIONS,
                InventoryPermissions::CREATE_TRANSACTIONS,
                InventoryPermissions::EDIT_TRANSACTIONS,
            ],
            self::AUDITOR => [
                // Read-only access
                InventoryPermissions::VIEW_STORES,
                InventoryPermissions::VIEW_ITEMS,
                InventoryPermissions::VIEW_TRANSACTIONS,
                InventoryPermissions::VIEW_INVENTORY_REPORTS,
            ],
        ];
    }

    public static function getPermissionsForRole(string $role): array
    {
        return self::permissions()[$role] ?? [];
    }
}
