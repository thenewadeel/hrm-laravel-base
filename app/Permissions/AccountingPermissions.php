<?php

namespace App\Permissions;

class AccountingPermissions
{
    // Chart of Accounts Management
    const VIEW_CHART_OF_ACCOUNTS = 'accounting.chart_of_accounts.view';

    const CREATE_CHART_OF_ACCOUNTS = 'accounting.chart_of_accounts.create';

    const EDIT_CHART_OF_ACCOUNTS = 'accounting.chart_of_accounts.edit';

    const DELETE_CHART_OF_ACCOUNTS = 'accounting.chart_of_accounts.delete';

    // Journal Entries Management
    const VIEW_JOURNAL_ENTRIES = 'accounting.journal_entries.view';

    const CREATE_JOURNAL_ENTRIES = 'accounting.journal_entries.create';

    const EDIT_JOURNAL_ENTRIES = 'accounting.journal_entries.edit';

    const DELETE_JOURNAL_ENTRIES = 'accounting.journal_entries.delete';

    const APPROVE_JOURNAL_ENTRIES = 'accounting.journal_entries.approve';

    const REJECT_JOURNAL_ENTRIES = 'accounting.journal_entries.reject';

    // Financial Transactions
    const VIEW_TRANSACTIONS = 'accounting.transactions.view';

    const CREATE_TRANSACTIONS = 'accounting.transactions.create';

    const EDIT_TRANSACTIONS = 'accounting.transactions.edit';

    const DELETE_TRANSACTIONS = 'accounting.transactions.delete';

    const APPROVE_TRANSACTIONS = 'accounting.transactions.approve';

    const REJECT_TRANSACTIONS = 'accounting.transactions.reject';

    // Budget Management
    const VIEW_BUDGETS = 'accounting.budgets.view';

    const CREATE_BUDGETS = 'accounting.budgets.create';

    const EDIT_BUDGETS = 'accounting.budgets.edit';

    const DELETE_BUDGETS = 'accounting.budgets.delete';

    const APPROVE_BUDGETS = 'accounting.budgets.approve';

    // Expense Management
    const VIEW_EXPENSES = 'accounting.expenses.view';

    const CREATE_EXPENSES = 'accounting.expenses.create';

    const EDIT_EXPENSES = 'accounting.expenses.edit';

    const DELETE_EXPENSES = 'accounting.expenses.delete';

    const APPROVE_EXPENSES = 'accounting.expenses.approve';

    const REJECT_EXPENSES = 'accounting.expenses.reject';

    // Invoice Management
    const VIEW_INVOICES = 'accounting.invoices.view';

    const CREATE_INVOICES = 'accounting.invoices.create';

    const EDIT_INVOICES = 'accounting.invoices.edit';

    const DELETE_INVOICES = 'accounting.invoices.delete';

    const APPROVE_INVOICES = 'accounting.invoices.approve';

    // Payment Management
    const VIEW_PAYMENTS = 'accounting.payments.view';

    const CREATE_PAYMENTS = 'accounting.payments.create';

    const EDIT_PAYMENTS = 'accounting.payments.edit';

    const DELETE_PAYMENTS = 'accounting.payments.delete';

    const APPROVE_PAYMENTS = 'accounting.payments.approve';

    // Cash Receipts Management
    const VIEW_CASH_RECEIPTS = 'accounting.cash_receipts.view';

    const CREATE_CASH_RECEIPTS = 'accounting.cash_receipts.create';

    const EDIT_CASH_RECEIPTS = 'accounting.cash_receipts.edit';

    const DELETE_CASH_RECEIPTS = 'accounting.cash_receipts.delete';

    // Cash Payments Management
    const VIEW_CASH_PAYMENTS = 'accounting.cash_payments.view';

    const CREATE_CASH_PAYMENTS = 'accounting.cash_payments.create';

    const EDIT_CASH_PAYMENTS = 'accounting.cash_payments.edit';

    const DELETE_CASH_PAYMENTS = 'accounting.cash_payments.delete';

    // Cash Reports
    const VIEW_CASH_REPORTS = 'accounting.cash_reports.view';

    const GENERATE_CASH_REPORTS = 'accounting.cash_reports.generate';

    // Voucher Management
    const VIEW_VOUCHERS = 'accounting.vouchers.view';

    const CREATE_VOUCHERS = 'accounting.vouchers.create';

    const EDIT_VOUCHERS = 'accounting.vouchers.edit';

    const DELETE_VOUCHERS = 'accounting.vouchers.delete';

    const POST_VOUCHERS = 'accounting.vouchers.post';

    // Financial Reports
    const VIEW_FINANCIAL_REPORTS = 'accounting.reports.view';

    const GENERATE_FINANCIAL_REPORTS = 'accounting.reports.generate';

    const EXPORT_FINANCIAL_DATA = 'accounting.data.export';

    // Audit Trail
    const VIEW_AUDIT_LOGS = 'accounting.audit.view';

    const EXPORT_AUDIT_LOGS = 'accounting.audit.export';

    // Approval Workflows
    const MANAGE_APPROVAL_WORKFLOWS = 'accounting.workflows.manage';

    const VIEW_APPROVAL_QUEUE = 'accounting.approvals.view';

    const PROCESS_APPROVALS = 'accounting.approvals.process';

    // All permissions array for easy reference
    public static function all(): array
    {
        return [
            // Chart of Accounts
            self::VIEW_CHART_OF_ACCOUNTS,
            self::CREATE_CHART_OF_ACCOUNTS,
            self::EDIT_CHART_OF_ACCOUNTS,
            self::DELETE_CHART_OF_ACCOUNTS,

            // Journal Entries
            self::VIEW_JOURNAL_ENTRIES,
            self::CREATE_JOURNAL_ENTRIES,
            self::EDIT_JOURNAL_ENTRIES,
            self::DELETE_JOURNAL_ENTRIES,
            self::APPROVE_JOURNAL_ENTRIES,
            self::REJECT_JOURNAL_ENTRIES,

            // Transactions
            self::VIEW_TRANSACTIONS,
            self::CREATE_TRANSACTIONS,
            self::EDIT_TRANSACTIONS,
            self::DELETE_TRANSACTIONS,
            self::APPROVE_TRANSACTIONS,
            self::REJECT_TRANSACTIONS,

            // Budgets
            self::VIEW_BUDGETS,
            self::CREATE_BUDGETS,
            self::EDIT_BUDGETS,
            self::DELETE_BUDGETS,
            self::APPROVE_BUDGETS,

            // Expenses
            self::VIEW_EXPENSES,
            self::CREATE_EXPENSES,
            self::EDIT_EXPENSES,
            self::DELETE_EXPENSES,
            self::APPROVE_EXPENSES,
            self::REJECT_EXPENSES,

            // Invoices
            self::VIEW_INVOICES,
            self::CREATE_INVOICES,
            self::EDIT_INVOICES,
            self::DELETE_INVOICES,
            self::APPROVE_INVOICES,

            // Payments
            self::VIEW_PAYMENTS,
            self::CREATE_PAYMENTS,
            self::EDIT_PAYMENTS,
            self::DELETE_PAYMENTS,
            self::APPROVE_PAYMENTS,

            // Cash Receipts
            self::VIEW_CASH_RECEIPTS,
            self::CREATE_CASH_RECEIPTS,
            self::EDIT_CASH_RECEIPTS,
            self::DELETE_CASH_RECEIPTS,

            // Cash Payments
            self::VIEW_CASH_PAYMENTS,
            self::CREATE_CASH_PAYMENTS,
            self::EDIT_CASH_PAYMENTS,
            self::DELETE_CASH_PAYMENTS,

            // Vouchers
            self::VIEW_VOUCHERS,
            self::CREATE_VOUCHERS,
            self::EDIT_VOUCHERS,
            self::DELETE_VOUCHERS,
            self::POST_VOUCHERS,

            // Reports
            self::VIEW_FINANCIAL_REPORTS,
            self::GENERATE_FINANCIAL_REPORTS,
            self::EXPORT_FINANCIAL_DATA,

            // Audit
            self::VIEW_AUDIT_LOGS,
            self::EXPORT_AUDIT_LOGS,

            // Approval Workflows
            self::MANAGE_APPROVAL_WORKFLOWS,
            self::VIEW_APPROVAL_QUEUE,
            self::PROCESS_APPROVALS,
        ];
    }

    // Permission groups for role assignment
    public static function groups(): array
    {
        return [
            'chart_of_accounts_management' => [
                self::VIEW_CHART_OF_ACCOUNTS,
                self::CREATE_CHART_OF_ACCOUNTS,
                self::EDIT_CHART_OF_ACCOUNTS,
                self::DELETE_CHART_OF_ACCOUNTS,
            ],
            'journal_management' => [
                self::VIEW_JOURNAL_ENTRIES,
                self::CREATE_JOURNAL_ENTRIES,
                self::EDIT_JOURNAL_ENTRIES,
                self::DELETE_JOURNAL_ENTRIES,
                self::APPROVE_JOURNAL_ENTRIES,
                self::REJECT_JOURNAL_ENTRIES,
            ],
            'transaction_management' => [
                self::VIEW_TRANSACTIONS,
                self::CREATE_TRANSACTIONS,
                self::EDIT_TRANSACTIONS,
                self::DELETE_TRANSACTIONS,
                self::APPROVE_TRANSACTIONS,
                self::REJECT_TRANSACTIONS,
            ],
            'budget_management' => [
                self::VIEW_BUDGETS,
                self::CREATE_BUDGETS,
                self::EDIT_BUDGETS,
                self::DELETE_BUDGETS,
                self::APPROVE_BUDGETS,
            ],
            'expense_management' => [
                self::VIEW_EXPENSES,
                self::CREATE_EXPENSES,
                self::EDIT_EXPENSES,
                self::DELETE_EXPENSES,
                self::APPROVE_EXPENSES,
                self::REJECT_EXPENSES,
            ],
            'invoice_management' => [
                self::VIEW_INVOICES,
                self::CREATE_INVOICES,
                self::EDIT_INVOICES,
                self::DELETE_INVOICES,
                self::APPROVE_INVOICES,
            ],
            'payment_management' => [
                self::VIEW_PAYMENTS,
                self::CREATE_PAYMENTS,
                self::EDIT_PAYMENTS,
                self::DELETE_PAYMENTS,
                self::APPROVE_PAYMENTS,
            ],
            'cash_receipts_management' => [
                self::VIEW_CASH_RECEIPTS,
                self::CREATE_CASH_RECEIPTS,
                self::EDIT_CASH_RECEIPTS,
                self::DELETE_CASH_RECEIPTS,
            ],
            'cash_payments_management' => [
                self::VIEW_CASH_PAYMENTS,
                self::CREATE_CASH_PAYMENTS,
                self::EDIT_CASH_PAYMENTS,
                self::DELETE_CASH_PAYMENTS,
            ],
            'cash_reports' => [
                self::VIEW_CASH_REPORTS,
                self::GENERATE_CASH_REPORTS,
            ],
            'voucher_management' => [
                self::VIEW_VOUCHERS,
                self::CREATE_VOUCHERS,
                self::EDIT_VOUCHERS,
                self::DELETE_VOUCHERS,
                self::POST_VOUCHERS,
            ],
            'approval_workflows' => [
                self::MANAGE_APPROVAL_WORKFLOWS,
                self::VIEW_APPROVAL_QUEUE,
                self::PROCESS_APPROVALS,
            ],
            'reporting' => [
                self::VIEW_FINANCIAL_REPORTS,
                self::GENERATE_FINANCIAL_REPORTS,
                self::EXPORT_FINANCIAL_DATA,
                self::VIEW_AUDIT_LOGS,
                self::EXPORT_AUDIT_LOGS,
            ],
        ];
    }
}
