<?php

namespace App\Roles;

use App\Permissions\AccountingPermissions;

class AccountingRoles
{
    const SUPER_ADMIN = 'admin';
    const ACCOUNTING_MANAGER = 'accounting_manager';
    const SENIOR_ACCOUNTANT = 'senior_accountant';
    const ACCOUNTANT = 'accountant';
    const ACCOUNTS_PAYABLE_CLERK = 'ap_clerk';
    const ACCOUNTS_RECEIVABLE_CLERK = 'ar_clerk';
    const BUDGET_ANALYST = 'budget_analyst';
    const FINANCIAL_APPROVER = 'financial_approver';
    const AUDITOR = 'auditor';

    public static function permissions(): array
    {
        return [
            self::SUPER_ADMIN => [
                // All accounting permissions
                ...AccountingPermissions::all(),
            ],
            self::ACCOUNTING_MANAGER => [
                // Full access to all accounting functions
                ...AccountingPermissions::all(),
            ],
            self::SENIOR_ACCOUNTANT => [
                // Comprehensive accounting access with approval rights
                AccountingPermissions::VIEW_CHART_OF_ACCOUNTS,
                AccountingPermissions::CREATE_CHART_OF_ACCOUNTS,
                AccountingPermissions::EDIT_CHART_OF_ACCOUNTS,

                AccountingPermissions::VIEW_JOURNAL_ENTRIES,
                AccountingPermissions::CREATE_JOURNAL_ENTRIES,
                AccountingPermissions::EDIT_JOURNAL_ENTRIES,
                AccountingPermissions::APPROVE_JOURNAL_ENTRIES,
                AccountingPermissions::REJECT_JOURNAL_ENTRIES,

                AccountingPermissions::VIEW_TRANSACTIONS,
                AccountingPermissions::CREATE_TRANSACTIONS,
                AccountingPermissions::EDIT_TRANSACTIONS,
                AccountingPermissions::APPROVE_TRANSACTIONS,
                AccountingPermissions::REJECT_TRANSACTIONS,

                AccountingPermissions::VIEW_BUDGETS,
                AccountingPermissions::CREATE_BUDGETS,
                AccountingPermissions::EDIT_BUDGETS,

                AccountingPermissions::VIEW_EXPENSES,
                AccountingPermissions::APPROVE_EXPENSES,
                AccountingPermissions::REJECT_EXPENSES,

                AccountingPermissions::VIEW_INVOICES,
                AccountingPermissions::APPROVE_INVOICES,

                AccountingPermissions::VIEW_PAYMENTS,
                AccountingPermissions::APPROVE_PAYMENTS,

                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
                AccountingPermissions::GENERATE_FINANCIAL_REPORTS,
                AccountingPermissions::EXPORT_FINANCIAL_DATA,

                AccountingPermissions::VIEW_APPROVAL_QUEUE,
                AccountingPermissions::PROCESS_APPROVALS,
            ],
            self::ACCOUNTANT => [
                // Standard accounting operations
                AccountingPermissions::VIEW_CHART_OF_ACCOUNTS,

                AccountingPermissions::VIEW_JOURNAL_ENTRIES,
                AccountingPermissions::CREATE_JOURNAL_ENTRIES,
                AccountingPermissions::EDIT_JOURNAL_ENTRIES,

                AccountingPermissions::VIEW_TRANSACTIONS,
                AccountingPermissions::CREATE_TRANSACTIONS,
                AccountingPermissions::EDIT_TRANSACTIONS,

                AccountingPermissions::VIEW_BUDGETS,
                AccountingPermissions::CREATE_BUDGETS,
                AccountingPermissions::EDIT_BUDGETS,

                AccountingPermissions::VIEW_EXPENSES,
                AccountingPermissions::CREATE_EXPENSES,
                AccountingPermissions::EDIT_EXPENSES,

                AccountingPermissions::VIEW_INVOICES,
                AccountingPermissions::CREATE_INVOICES,
                AccountingPermissions::EDIT_INVOICES,

                AccountingPermissions::VIEW_PAYMENTS,
                AccountingPermissions::CREATE_PAYMENTS,
                AccountingPermissions::EDIT_PAYMENTS,

                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
                AccountingPermissions::GENERATE_FINANCIAL_REPORTS,

                AccountingPermissions::VIEW_APPROVAL_QUEUE,
            ],
            self::ACCOUNTS_PAYABLE_CLERK => [
                // AP-specific permissions
                AccountingPermissions::VIEW_EXPENSES,
                AccountingPermissions::CREATE_EXPENSES,
                AccountingPermissions::EDIT_EXPENSES,

                AccountingPermissions::VIEW_INVOICES,
                AccountingPermissions::CREATE_INVOICES,
                AccountingPermissions::EDIT_INVOICES,

                AccountingPermissions::VIEW_PAYMENTS,
                AccountingPermissions::CREATE_PAYMENTS,
                AccountingPermissions::EDIT_PAYMENTS,

                AccountingPermissions::VIEW_TRANSACTIONS,
                AccountingPermissions::CREATE_TRANSACTIONS,
                AccountingPermissions::EDIT_TRANSACTIONS,

                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
            ],
            self::ACCOUNTS_RECEIVABLE_CLERK => [
                // AR-specific permissions
                AccountingPermissions::VIEW_INVOICES,
                AccountingPermissions::CREATE_INVOICES,
                AccountingPermissions::EDIT_INVOICES,

                AccountingPermissions::VIEW_PAYMENTS,
                AccountingPermissions::CREATE_PAYMENTS,
                AccountingPermissions::EDIT_PAYMENTS,

                AccountingPermissions::VIEW_TRANSACTIONS,
                AccountingPermissions::CREATE_TRANSACTIONS,
                AccountingPermissions::EDIT_TRANSACTIONS,

                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
            ],
            self::BUDGET_ANALYST => [
                // Budget-focused permissions
                AccountingPermissions::VIEW_BUDGETS,
                AccountingPermissions::CREATE_BUDGETS,
                AccountingPermissions::EDIT_BUDGETS,
                AccountingPermissions::DELETE_BUDGETS,

                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
                AccountingPermissions::GENERATE_FINANCIAL_REPORTS,
                AccountingPermissions::EXPORT_FINANCIAL_DATA,

                AccountingPermissions::VIEW_EXPENSES,
                AccountingPermissions::VIEW_TRANSACTIONS,
            ],
            self::FINANCIAL_APPROVER => [
                // Approval-focused permissions
                AccountingPermissions::VIEW_APPROVAL_QUEUE,
                AccountingPermissions::PROCESS_APPROVALS,

                AccountingPermissions::VIEW_EXPENSES,
                AccountingPermissions::APPROVE_EXPENSES,
                AccountingPermissions::REJECT_EXPENSES,

                AccountingPermissions::VIEW_INVOICES,
                AccountingPermissions::APPROVE_INVOICES,

                AccountingPermissions::VIEW_PAYMENTS,
                AccountingPermissions::APPROVE_PAYMENTS,

                AccountingPermissions::VIEW_JOURNAL_ENTRIES,
                AccountingPermissions::APPROVE_JOURNAL_ENTRIES,
                AccountingPermissions::REJECT_JOURNAL_ENTRIES,

                AccountingPermissions::VIEW_TRANSACTIONS,
                AccountingPermissions::APPROVE_TRANSACTIONS,
                AccountingPermissions::REJECT_TRANSACTIONS,

                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
            ],
            self::AUDITOR => [
                // Read-only access for auditing
                AccountingPermissions::VIEW_CHART_OF_ACCOUNTS,
                AccountingPermissions::VIEW_JOURNAL_ENTRIES,
                AccountingPermissions::VIEW_TRANSACTIONS,
                AccountingPermissions::VIEW_BUDGETS,
                AccountingPermissions::VIEW_EXPENSES,
                AccountingPermissions::VIEW_INVOICES,
                AccountingPermissions::VIEW_PAYMENTS,
                AccountingPermissions::VIEW_FINANCIAL_REPORTS,
                AccountingPermissions::VIEW_AUDIT_LOGS,
                AccountingPermissions::EXPORT_AUDIT_LOGS,
            ],
        ];
    }

    public static function getPermissionsForRole(string $role): array
    {
        return self::permissions()[$role] ?? [];
    }
}
