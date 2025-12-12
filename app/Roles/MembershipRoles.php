<?php

namespace App\Roles;

use App\Permissions\MembershipPermissions;

class MembershipRoles
{
    public const MEMBERSHIP_ADMIN = 'membership.admin';

    public const MEMBERSHIP_MANAGER = 'membership.manager';

    public const MEMBERSHIP_CLERK = 'membership.clerk';

    public const MEMBERSHIP_VIEWER = 'membership.viewer';

    public static function permissions(): array
    {
        return [
            self::MEMBERSHIP_ADMIN => [
                // All membership permissions
                ...MembershipPermissions::all(),
            ],
            self::MEMBERSHIP_MANAGER => [
                // Member Management
                MembershipPermissions::VIEW_MEMBERS,
                MembershipPermissions::CREATE_MEMBERS,
                MembershipPermissions::EDIT_MEMBERS,
                MembershipPermissions::MANAGE_MEMBERS,

                // Fee Management
                MembershipPermissions::VIEW_FEES,
                MembershipPermissions::CREATE_FEES,
                MembershipPermissions::EDIT_FEES,
                MembershipPermissions::MANAGE_FEES,
                MembershipPermissions::PROCESS_PAYMENTS,

                // Subscription Management
                MembershipPermissions::VIEW_SUBSCRIPTIONS,
                MembershipPermissions::CREATE_SUBSCRIPTIONS,
                MembershipPermissions::EDIT_SUBSCRIPTIONS,
                MembershipPermissions::MANAGE_SUBSCRIPTIONS,
                MembershipPermissions::RENEW_SUBSCRIPTIONS,

                // Card Management
                MembershipPermissions::PRINT_CARDS,
                MembershipPermissions::DESIGN_CARDS,

                // Reports
                MembershipPermissions::VIEW_REPORTS,
                MembershipPermissions::GENERATE_REPORTS,
                MembershipPermissions::EXPORT_DATA,

                // Dashboard Access
                MembershipPermissions::VIEW_DASHBOARD,
            ],
            self::MEMBERSHIP_CLERK => [
                // Member Management
                MembershipPermissions::VIEW_MEMBERS,
                MembershipPermissions::CREATE_MEMBERS,
                MembershipPermissions::EDIT_MEMBERS,

                // Fee Management
                MembershipPermissions::VIEW_FEES,
                MembershipPermissions::PROCESS_PAYMENTS,

                // Subscription Management
                MembershipPermissions::VIEW_SUBSCRIPTIONS,
                MembershipPermissions::RENEW_SUBSCRIPTIONS,

                // Card Management
                MembershipPermissions::PRINT_CARDS,

                // Dashboard Access
                MembershipPermissions::VIEW_DASHBOARD,
            ],
            self::MEMBERSHIP_VIEWER => [
                // Read-only access
                MembershipPermissions::VIEW_MEMBERS,
                MembershipPermissions::VIEW_FEES,
                MembershipPermissions::VIEW_SUBSCRIPTIONS,
                MembershipPermissions::VIEW_REPORTS,
                MembershipPermissions::VIEW_DASHBOARD,
            ],
        ];
    }

    public static function getPermissionsForRole(string $role): array
    {
        return self::permissions()[$role] ?? [];
    }
}
