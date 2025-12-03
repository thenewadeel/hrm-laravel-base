<?php

namespace App\Permissions;

class MembershipPermissions
{
    // Member Management
    public const VIEW_MEMBERS = 'membership.view_members';
    public const CREATE_MEMBERS = 'membership.create_members';
    public const EDIT_MEMBERS = 'membership.edit_members';
    public const DELETE_MEMBERS = 'membership.delete_members';
    public const MANAGE_MEMBERS = 'membership.manage_members';

    // Fee Management
    public const VIEW_FEES = 'membership.view_fees';
    public const CREATE_FEES = 'membership.create_fees';
    public const EDIT_FEES = 'membership.edit_fees';
    public const DELETE_FEES = 'membership.delete_fees';
    public const MANAGE_FEES = 'membership.manage_fees';
    public const PROCESS_PAYMENTS = 'membership.process_payments';
    public const WAIVE_FEES = 'membership.waive_fees';

    // Subscription Management
    public const VIEW_SUBSCRIPTIONS = 'membership.view_subscriptions';
    public const CREATE_SUBSCRIPTIONS = 'membership.create_subscriptions';
    public const EDIT_SUBSCRIPTIONS = 'membership.edit_subscriptions';
    public const DELETE_SUBSCRIPTIONS = 'membership.delete_subscriptions';
    public const MANAGE_SUBSCRIPTIONS = 'membership.manage_subscriptions';
    public const RENEW_SUBSCRIPTIONS = 'membership.renew_subscriptions';
    public const CANCEL_SUBSCRIPTIONS = 'membership.cancel_subscriptions';

    // Card Management
    public const PRINT_CARDS = 'membership.print_cards';
    public const DESIGN_CARDS = 'membership.design_cards';
    public const BATCH_PRINT_CARDS = 'membership.batch_print_cards';

    // Reports
    public const VIEW_REPORTS = 'membership.view_reports';
    public const GENERATE_REPORTS = 'membership.generate_reports';
    public const EXPORT_DATA = 'membership.export_data';

    // Dashboard Access
    public const VIEW_DASHBOARD = 'membership.view_dashboard';

    // Admin
    public const ADMIN = 'membership.admin';
}