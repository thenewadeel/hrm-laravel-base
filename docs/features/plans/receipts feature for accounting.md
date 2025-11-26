# Cash Receipts & Payments Module - Implementation Plan

## Feature Overview

Implement comprehensive cash management system with receipt printing for deposits and voucher printing for payments, fully integrated with existing double-entry accounting system.

## Phase 1: Database & Models

### 1.1 Database Migrations

-   Create `cash_receipts` table with fields: receipt_number, date, received_from, amount, cash_account_id, credit_account_id, description, notes
-   Create `cash_payments` table with fields: voucher_number, date, paid_to, amount, cash_account_id, debit_account_id, purpose, notes
-   Add organization_id, soft deletes, timestamps to both tables
-   Create indexes for receipt_number, voucher_number, date ranges

### 1.2 Eloquent Models

-   Create `CashReceipt` model in `App\Models\Accounting\`
-   Create `CashPayment` model in `App\Models\Accounting\`
-   Implement `BelongsToOrganization` trait
-   Define relationships: belongsTo CashAccount, CreditAccount, DebitAccount
-   Add automatic journal entry creation in model events

### 1.3 Model Factories & Seeders

-   Create factories for both models with realistic test data
-   Update `DemoDataSeeder` to include sample cash transactions

## Phase 2: Core Business Logic

### 2.1 Services

-   Create `CashReceiptService` with methods: createReceipt, validateAmount, generateReceiptNumber
-   Create `CashPaymentService` with methods: createPayment, validatePayment, generateVoucherNumber
-   Implement automatic journal entry creation in services
-   Add validation for sufficient cash balance before payments

### 2.2 Integration with Double-Entry

-   Automatic JournalEntry creation on cash transaction save
-   Debit cash account for receipts, credit cash account for payments
-   Maintain balanced entries according to accounting principles
-   Link journal entries to cash transactions via polymorphic relationship

## Phase 3: Livewire Components

### 3.1 Cash Receipts

-   `CashReceipts/Index` - List with filters (date range, search)
-   `CashReceipts/Create` - Form with account selection, validation
-   `CashReceipts/Edit` - Edit existing receipts (with restrictions)
-   `CashReceipts/PrintReceipt` - Printable receipt format

### 3.2 Cash Payments

-   `CashPayments/Index` - List with filters and status
-   `CashPayments/Create` - Payment form with vendor/expense account selection
-   `CashPayments/Edit` - Edit payments (with audit trail)
-   `CashPayments/PrintVoucher` - Printable voucher format

### 3.3 Reporting Components

-   `CashTransactions/DailySummary` - Today's cash flow summary
-   `CashTransactions/MonthlySummary` - Monthly reports with charts
-   `CashTransactions/CashPosition` - Current cash balance across accounts

## Phase 4: Reporting & Analytics

### 4.1 Daily Summary

-   Total receipts and payments for the day
-   Opening and closing cash balance
-   Breakdown by cash account
-   List of all transactions for the day

### 4.2 Monthly Summary

-   Monthly cash flow statement
-   Comparison with previous month
-   Expense categorization
-   Revenue source analysis

### 4.3 Export Features

-   Export to PDF for daily/monthly reports
-   CSV export for accounting software integration
-   Print-friendly formats for all summaries

## Phase 5: Printing & Documentation

### 5.1 Receipt Printing

-   Professional receipt layout with company header
-   Sequential receipt numbering
-   Digital signature/approval fields
-   Duplicate copy support

### 5.2 Voucher Printing

-   Voucher format with approval signatures
-   Purpose and description details
-   Payment method and reference
-   Supporting document references

## Phase 6: Testing & Quality Assurance

### 6.1 Feature Tests

-   Test cash receipt creation and journal entry generation
-   Test cash payment validation and sufficient funds
-   Test receipt/voucher number sequencing
-   Test daily/monthly summary calculations

### 6.2 Unit Tests

-   Test CashReceiptService business logic
-   Test CashPaymentService validation rules
-   Test automatic journal entry balancing
-   Test reporting calculations

### 6.3 Integration Tests

-   Test with existing chart of accounts
-   Test multi-organization data isolation
-   Test with demo data seeder
-   Test printing functionality

## Technical Specifications

### Database Relations

```
cash_receipts
- organization_id → organizations.id
- cash_account_id → chart_of_accounts.id
- credit_account_id → chart_of_accounts.id

cash_payments
- organization_id → organizations.id
- cash_account_id → chart_of_accounts.id
- debit_account_id → chart_of_accounts.id
```

### Key Business Rules

-   Receipt numbers: Auto-increment prefixed with "RCPT-"
-   Voucher numbers: Auto-increment prefixed with "VCH-"
-   Amounts must be positive and validated against account balances
-   Transactions cannot be deleted, only voided with reversal entries
-   All operations must maintain double-entry integrity

### Security & Permissions

-   New permissions: `cash_receipts.create`, `cash_payments.create`, `cash_reports.view`
-   Integration with existing role-based access control
-   Audit trail for all cash transactions
-   Organization-level data isolation maintained

## Dependencies & Integration Points

### Existing Modules Used

-   Chart of Accounts module (account selection)
-   Journal Entries system (automatic posting)
-   Organization management (multi-tenancy)
-   User authentication & permissions

### New Routes Required

```
/cash/receipts
/cash/receipts/create
/cash/receipts/{id}/print
/cash/payments
/cash/payments/create
/cash/payments/{id}/print
/cash/reports/daily
/cash/reports/monthly
```

## Success Criteria

-   All cash transactions automatically create balanced journal entries
-   Receipts and vouchers print in professional format
-   Daily and monthly summaries accurately reflect cash position
-   Integration with existing chart of accounts works seamlessly
-   Multi-tenant data isolation maintained
-   85%+ test coverage achieved

## Risk Mitigation

-   Backup existing data before migrations
-   Test thoroughly with demo data first
-   Implement transaction rollback for failed operations
-   Validate all accounting rules before deployment

This plan provides a comprehensive roadmap for implementing the cash receipts and payments system while fully leveraging your existing double-entry accounting infrastructure.
