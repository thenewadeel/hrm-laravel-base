<?php

namespace App\Permissions;

class InventoryPermissions
{
    // Store Management
    const VIEW_STORES = 'inventory.stores.view';
    const CREATE_STORES = 'inventory.stores.create';
    const EDIT_STORES = 'inventory.stores.edit';
    const DELETE_STORES = 'inventory.stores.delete';
    const MANAGE_STORE_INVENTORY = 'inventory.stores.manage-inventory';

    // Item Management
    const VIEW_ITEMS = 'inventory.items.view';
    const CREATE_ITEMS = 'inventory.items.create';
    const EDIT_ITEMS = 'inventory.items.edit';
    const DELETE_ITEMS = 'inventory.items.delete';

    // Transaction Management
    const VIEW_TRANSACTIONS = 'inventory.transactions.view';
    const CREATE_TRANSACTIONS = 'inventory.transactions.create';
    const EDIT_TRANSACTIONS = 'inventory.transactions.edit';
    const DELETE_TRANSACTIONS = 'inventory.transactions.delete';
    const FINALIZE_TRANSACTIONS = 'inventory.transactions.finalize';
    const CANCEL_TRANSACTIONS = 'inventory.transactions.cancel';

    // Reports
    const VIEW_INVENTORY_REPORTS = 'inventory.reports.view';
    const EXPORT_INVENTORY_DATA = 'inventory.data.export';

    // All permissions array for easy reference
    public static function all(): array
    {
        return [
            self::VIEW_STORES,
            self::CREATE_STORES,
            self::EDIT_STORES,
            self::DELETE_STORES,
            self::MANAGE_STORE_INVENTORY,

            self::VIEW_ITEMS,
            self::CREATE_ITEMS,
            self::EDIT_ITEMS,
            self::DELETE_ITEMS,

            self::VIEW_TRANSACTIONS,
            self::CREATE_TRANSACTIONS,
            self::EDIT_TRANSACTIONS,
            self::DELETE_TRANSACTIONS,
            self::FINALIZE_TRANSACTIONS,
            self::CANCEL_TRANSACTIONS,

            self::VIEW_INVENTORY_REPORTS,
            self::EXPORT_INVENTORY_DATA,
        ];
    }

    // Permission groups for role assignment
    public static function groups(): array
    {
        return [
            'store_management' => [
                self::VIEW_STORES,
                self::CREATE_STORES,
                self::EDIT_STORES,
                self::DELETE_STORES,
                self::MANAGE_STORE_INVENTORY,
            ],
            'item_management' => [
                self::VIEW_ITEMS,
                self::CREATE_ITEMS,
                self::EDIT_ITEMS,
                self::DELETE_ITEMS,
            ],
            'transaction_management' => [
                self::VIEW_TRANSACTIONS,
                self::CREATE_TRANSACTIONS,
                self::EDIT_TRANSACTIONS,
                self::DELETE_TRANSACTIONS,
                self::FINALIZE_TRANSACTIONS,
                self::CANCEL_TRANSACTIONS,
            ],
            'reporting' => [
                self::VIEW_INVENTORY_REPORTS,
                self::EXPORT_INVENTORY_DATA,
            ],
        ];
    }
}
