<?php

namespace App\Permissions;

class OrganizationPermissions
{
    // User Management
    const VIEW_USERS = 'organization.users.view';
    const CREATE_USERS = 'organization.users.create';
    const EDIT_USERS = 'organization.users.edit';
    const DELETE_USERS = 'organization.users.delete';
    // const MANAGE_USER_INVENTORY = 'organization.users.manage-inventory';

    // organization Management
    const VIEW_ORGANIZATION = 'organization.view';
    const CREATE_ORGANIZATION = 'organization.create';
    const EDIT_ORGANIZATION = 'organization.edit';
    const DELETE_ORGANIZATION = 'organization.delete';

    // organization_unit Management
    const VIEW_ORGANIZATION_UNITS = 'organization.organization_units.view';
    const CREATE_ORGANIZATION_UNITS = 'organization.organization_units.create';
    const EDIT_ORGANIZATION_UNITS = 'organization.organization_units.edit';
    const DELETE_ORGANIZATION_UNITS = 'organization.organization_units.delete';

    // organization_user Management
    const VIEW_ORGANIZATION_USERS = 'organization.organization_users.view';
    const ASSIGN_ORGANIZATION_USERS = 'organization.organization_users.assign';
    const CREATE_ORGANIZATION_USERS = 'organization.organization_users.create';
    const EDIT_ORGANIZATION_USERS = 'organization.organization_users.edit';
    const DELETE_ORGANIZATION_USERS = 'organization.organization_users.delete';

    // Reports
    const VIEW_ORGANIZATION_REPORTS = 'organization.reports.view';
    const EXPORT_ORGANIZATION_DATA = 'organization.data.export';

    // All permissions array for easy reference
    public static function all(): array
    {
        return [
            self::VIEW_USERS,
            self::CREATE_USERS,
            self::EDIT_USERS,
            self::DELETE_USERS,
            // self::MANAGE_USER_INVENTORY,

            self::VIEW_ORGANIZATION,
            self::CREATE_ORGANIZATION,
            self::EDIT_ORGANIZATION,
            self::DELETE_ORGANIZATION,

            self::VIEW_ORGANIZATION_UNITS,
            self::CREATE_ORGANIZATION_UNITS,
            self::EDIT_ORGANIZATION_UNITS,
            self::DELETE_ORGANIZATION_UNITS,

            self::VIEW_ORGANIZATION_USERS,
            self::ASSIGN_ORGANIZATION_USERS,
            self::CREATE_ORGANIZATION_USERS,
            self::EDIT_ORGANIZATION_USERS,
            self::DELETE_ORGANIZATION_USERS,

            self::VIEW_ORGANIZATION_REPORTS,
            self::EXPORT_ORGANIZATION_DATA,
        ];
    }

    // Permission groups for role assignment
    public static function groups(): array
    {
        return [
            'organization_management' => [
                self::VIEW_ORGANIZATION,
                self::CREATE_ORGANIZATION,
                self::EDIT_ORGANIZATION,
                self::DELETE_ORGANIZATION,
            ],
            'user_management' => [
                self::VIEW_USERS,
                self::CREATE_USERS,
                self::EDIT_USERS,
                self::DELETE_USERS,
                // self::MANAGE_USER_INVENTORY,
            ],
            'organization_unit_management' => [
                self::VIEW_ORGANIZATION_UNITS,
                self::CREATE_ORGANIZATION_UNITS,
                self::EDIT_ORGANIZATION_UNITS,
                self::DELETE_ORGANIZATION_UNITS,
            ],
            'reporting' => [
                self::VIEW_ORGANIZATION_REPORTS,
                self::EXPORT_ORGANIZATION_DATA,
            ],
        ];
    }
}
