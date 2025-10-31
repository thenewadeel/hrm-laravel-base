<?php

namespace App\Roles;

use App\Permissions\OrganizationPermissions;

class OrganizationRoles
{
    const SUPER_ADMIN = 'admin';
    const ORGANIZATION_ADMIN = 'organization_admin';
    const ORGANIZATION_UNIT_MANAGER = 'organization_unit_manager';
    const USER_MANAGER = 'user_manager';
    const AUDITOR = 'auditor';

    public static function permissions(): array
    {
        return [
            self::SUPER_ADMIN => [
                // All inventory permissions
                ...OrganizationPermissions::all(),
            ],
            self::ORGANIZATION_ADMIN => [
                // All inventory permissions
                ...OrganizationPermissions::all(),
                //Organization
                OrganizationPermissions::VIEW_ORGANIZATION,
                OrganizationPermissions::CREATE_ORGANIZATION,
                OrganizationPermissions::EDIT_ORGANIZATION,
                OrganizationPermissions::DELETE_ORGANIZATION,
                // Reporting
                OrganizationPermissions::VIEW_ORGANIZATION_REPORTS,
                OrganizationPermissions::EXPORT_ORGANIZATION_DATA,
            ],
            self::ORGANIZATION_UNIT_MANAGER => [
                // organization_unit management
                OrganizationPermissions::VIEW_ORGANIZATION_UNITS,
                OrganizationPermissions::CREATE_ORGANIZATION_UNITS,
                OrganizationPermissions::EDIT_ORGANIZATION_UNITS,
                OrganizationPermissions::DELETE_ORGANIZATION_UNITS,
                // OrganizationPermissions::CANCEL_ORGANIZATION_UNITS,
            ],
            self::USER_MANAGER => [
                OrganizationPermissions::VIEW_USERS,
                OrganizationPermissions::CREATE_USERS,
                OrganizationPermissions::EDIT_USERS,
                // OrganizationPermissions::DELETE_USERS,
                // OrganizationPermissions::MANAGE_USER_INVENTORY,
                OrganizationPermissions::VIEW_ORGANIZATION_USERS,
                OrganizationPermissions::ASSIGN_ORGANIZATION_USERS,
                OrganizationPermissions::CREATE_ORGANIZATION_USERS,
                OrganizationPermissions::EDIT_ORGANIZATION_USERS,
                OrganizationPermissions::DELETE_ORGANIZATION_USERS,

            ],
            self::AUDITOR => [
                // Read-only access
                OrganizationPermissions::VIEW_USERS,
                OrganizationPermissions::VIEW_ORGANIZATION_UNITS,
                OrganizationPermissions::VIEW_ORGANIZATION_REPORTS,
            ],
        ];
    }

    public static function getPermissionsForRole(string $role): array
    {
        return self::permissions()[$role] ?? [];
    }
}
