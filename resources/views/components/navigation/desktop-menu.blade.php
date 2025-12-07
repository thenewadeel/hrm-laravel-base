{{-- Dashboard --}}
<x-navigation.link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="🏠">
    Dashboard
</x-navigation.link>
{{-- Inventory Management --}}
<x-navigation.dropdown align="left" width="56">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="📦" :active="request()->routeIs('inventory.*')">
            Inventory
            <svg class="ml-1 -mr-0.5 h-4 w-4 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </x-navigation.link>
    </x-slot>

    <!-- Inventory Management -->
    <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
        Inventory Management
    </div>

    <x-navigation.dropdown-link href="{{ route('inventory.items.index') }}" icon="📦">
        Items
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.stores.index') }}" icon="🏪">
        Stores
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.transactions.index') }}" icon="📋">
        Transactions
    </x-navigation.dropdown-link>

    <!-- Stock Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Stock Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('inventory.stock.adjustment') }}" icon="⚖️">
        Stock Adjustment
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.stock.count') }}" icon="🔢">
        Stock Count
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.stock.transfer') }}" icon="🔄">
        Stock Transfer
    </x-navigation.dropdown-link>

    <!-- Inventory Reports -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Reports
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.stock-levels') }}" icon="📊">
        Stock Levels
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.low-stock') }}" icon="⚠️">
        Low Stock Report
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.movement') }}" icon="📈">
        Stock Movement
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Financial Management --}}
<x-navigation.dropdown align="left" width="64">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="💰" :active="request()->routeIs('accounts.*') || request()->routeIs('accounting.*')">
            Accounting
            <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </x-navigation.link>
    </x-slot>

    <!-- Core Accounting -->
    <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
        Core Accounting
    </div>

    <x-navigation.dropdown-link href="{{ route('accounts.index') }}" icon="📊">
        Chart of Accounts
    </x-navigation.dropdown-link>

    <!-- Vouchers -->
    <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
        Vouchers
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.vouchers.sales.create') }}" icon="🧾">
        Sales Voucher
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.vouchers.purchase.create') }}" icon="🛒">
        Purchase Voucher
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.vouchers.expense.create') }}" icon="💸">
        Expense Voucher
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.vouchers.salary.create') }}" icon="💰">
        Salary Voucher
    </x-navigation.dropdown-link>

    <!-- Bank Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Bank Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.bank-accounts.index') }}" icon="🏦">
        Bank Accounts
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.bank-statements.index') }}" icon="📄">
        Bank Statements
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.bank-reconciliation.index') }}" icon="🔄">
        Bank Reconciliation
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.bank-transactions.index') }}" icon="💳">
        Bank Transactions
    </x-navigation.dropdown-link>

    <!-- Cash Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Cash Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.cash-receipts.index') }}" icon="💵">
        Cash Receipts
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.cash-payments.index') }}" icon="💸">
        Cash Payments
    </x-navigation.dropdown-link>

    <!-- Outstanding Statements -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Outstanding Statements
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.outstanding.receivables') }}" icon="📈">
        Receivables Outstanding
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.outstanding.payables') }}" icon="📉">
        Payables Outstanding
    </x-navigation.dropdown-link>

    <!-- Advanced Accounting -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Advanced Accounting
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.fixed-assets.index') }}" icon="🏢">
        Fixed Assets
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.financial-years.index') }}" icon="📅">
        Financial Years
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.tax.reporting.dashboard') }}" icon="🧾">
        Tax Management
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Reports & Analytics --}}
<x-navigation.dropdown align="left" width="64">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="📊" :active="request()->routeIs('*.reports.*') || request()->routeIs('reports.*')">
            Reports & Analytics
            <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </x-navigation.link>
    </x-slot>

    <!-- Financial Reports -->
    <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
        Financial Reports
    </div>

    <x-navigation.dropdown-link href="#" icon="📊">
        Balance Sheet
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="#" icon="📈">
        Income Statement
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="#" icon="⚖️">
        Trial Balance
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.download.trial-balance') }}" icon="📄">
        Download Trial Balance
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.download.balance-sheet') }}" icon="📄">
        Download Balance Sheet
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.download.income-statement') }}" icon="📄">
        Download Income Statement
    </x-navigation.dropdown-link>

    <!-- Inventory Reports -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Inventory Reports
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.stock-levels') }}" icon="📦">
        Stock Levels Report
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.low-stock') }}" icon="⚠️">
        Low Stock Report
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.movement') }}" icon="🔄">
        Stock Movement Report
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.download.stock-levels') }}" icon="📄">
        Download Stock Report
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.download.movement') }}" icon="📄">
        Download Movement Report
    </x-navigation.dropdown-link>

    <!-- HR & Payroll Reports -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            HR & Payroll Reports
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('attendance.export-payroll') }}" icon="📊">
        Attendance Export
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.advance-reports') }}" icon="💵">
        Advance Reports
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.report') }}" icon="📈">
        Payroll Reports
    </x-navigation.dropdown-link>

    <!-- Tax & Compliance Reports -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Tax & Compliance
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.tax.download.tax-report') }}" icon="🧾">
        Tax Reports
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.tax.download.tax-liability') }}" icon="📋">
        Tax Liability Report
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.tax.download.filing-schedule') }}" icon="📅">
        Tax Filing Schedule
    </x-navigation.dropdown-link>

    <!-- Asset Reports -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Asset Reports
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.fixed-assets.download.asset-register') }}" icon="🏢">
        Asset Register
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.fixed-assets.download.depreciation-schedule') }}" icon="📉">
        Depreciation Schedule
    </x-navigation.dropdown-link>

    <!-- Bank Reports -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Bank Reports
        </div>
    </div>

    {{-- These routes require parameters, so we'll use placeholder for now --}}
    <x-navigation.dropdown-link href="#" icon="🏦">
        Bank Transactions
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="#" icon="📄">
        Bank Statement
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="#" icon="🔄">
        Bank Reconciliation Report
    </x-navigation.dropdown-link>

    <!-- Outstanding Statements -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Outstanding Statements
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('accounting.download.receivables-outstanding') }}" icon="📈">
        Receivables Outstanding
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('accounting.download.payables-outstanding') }}" icon="📉">
        Payables Outstanding
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Human Resources --}}
<x-navigation.dropdown align="left" width="56">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="👥" :active="request()->routeIs('hr.*') || request()->routeIs('attendance.*') || request()->routeIs('payroll.*')">
            Human Resources
            <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </x-navigation.link>
    </x-slot>

    <!-- Employee Management -->
    <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
        Employee Management
    </div>

    <x-navigation.dropdown-link href="{{ route('hr.employees.index') }}" icon="👥">
        Employees
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('hr.positions.index') }}" icon="💼">
        Job Positions
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('hr.shifts.index') }}" icon="⏰">
        Shifts
    </x-navigation.dropdown-link>

    <!-- Attendance Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Attendance Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('attendance.dashboard') }}" icon="⏱️">
        Attendance Dashboard
    </x-navigation.dropdown-link>

    <!-- Payroll Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Payroll Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('payroll.dashboard') }}" icon="💼">
        Payroll Dashboard
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.processing') }}" icon="⚙️">
        Payroll Processing
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.advances') }}" icon="💵">
        Salary Advances
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.loans') }}" icon="🏦">
        Employee Loans
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.increments') }}" icon="📈">
        Employee Increments
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.tax') }}" icon="🧾">
        Payroll Tax
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Organization Management --}}
<x-navigation.dropdown align="left" width="56">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="🏢" :active="request()->routeIs('organization.*') || request()->routeIs('organizations.*')">
            Organization
            <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </x-navigation.link>
    </x-slot>

    <x-navigation.dropdown-link href="{{ route('organization.index') }}" icon="🏢">
        Organizations
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('organization.dashboard') }}" icon="📊">
        Organization Dashboard
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('organization.analytics') }}" icon="📈">
        Analytics
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('organization.structure') }}" icon="🏗️">
        Organization Structure
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Membership System --}}
<x-navigation.dropdown align="left" width="56">
    <x-slot name="trigger">
        <x-navigation.link href="#" icon="👥" :active="request()->routeIs('members.*') || request()->routeIs('membership.*') || request()->routeIs('cards.*') || request()->routeIs('fees.*') || request()->routeIs('subscriptions.*')">
            Membership
            <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd" />
            </svg>
        </x-navigation.link>
    </x-slot>

    <!-- Member Management -->
    <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
        Member Management
    </div>

    <x-navigation.dropdown-link href="{{ route('members.index') }}" icon="👥">
        Members
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('membership.dashboard') }}" icon="🏠">
        Membership Dashboard
    </x-navigation.dropdown-link>

    <!-- Card Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Card Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('cards.index') }}" icon="🆔">
        Membership Cards
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('cards.templates') }}" icon="🎨">
        Card Templates
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('cards.settings') }}" icon="⚙️">
        Card Settings
    </x-navigation.dropdown-link>

    <!-- Fee Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Fee Management
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('fees.index') }}" icon="💰">
        Fees
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('fees.statistics') }}" icon="📊">
        Fee Statistics
    </x-navigation.dropdown-link>

    <!-- Subscription Management -->
    <div class="border-t border-secondary mt-2 pt-2">
        <div class="px-4 py-2 text-xs font-semibold text-muted uppercase tracking-wider">
            Subscriptions
        </div>
    </div>

    <x-navigation.dropdown-link href="{{ route('subscriptions.index') }}" icon="🔄">
        Subscriptions
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('subscriptions.statistics') }}" icon="📈">
        Subscription Statistics
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('subscriptions.expiring') }}" icon="⚠️">
        Expiring Soon
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Admin Portal --}}
@if (auth()->check() && auth()->user()->hasRole('admin'))
    <x-navigation.dropdown align="left" width="56">
        <x-slot name="trigger">
            <x-navigation.link href="#" icon="🔧" :active="request()->routeIs('admin.*')">
                Admin Portal
                <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </x-navigation.link>
        </x-slot>

        <x-navigation.dropdown-link href="{{ route('admin.dashboard') }}" icon="📊">
            Admin Dashboard
        </x-navigation.dropdown-link>

        <x-navigation.dropdown-link href="{{ route('admin.attach-user') }}" icon="👥">
            Attach User
        </x-navigation.dropdown-link>

        <x-navigation.dropdown-link href="{{ route('admin.detach-user') }}" icon="🔗">
            Detach User
        </x-navigation.dropdown-link>

        <x-navigation.dropdown-link href="#" icon="🔧">
            Fix User Organization
        </x-navigation.dropdown-link>
    </x-navigation.dropdown>
@endif

{{-- System Setup --}}
@if (auth()->check() && auth()->user()->hasRole(['admin', 'manager']))
    <x-navigation.dropdown align="left" width="56">
        <x-slot name="trigger">
            <x-navigation.link href="#" icon="⚙️" :active="request()->routeIs('setup.*')">
                System Setup
                <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </x-navigation.link>
        </x-slot>

        <x-navigation.dropdown-link href="{{ route('setup.organization') }}" icon="🏢">
            Organization Setup
        </x-navigation.dropdown-link>

        <x-navigation.dropdown-link href="{{ route('setup.accounts') }}" icon="📊">
            Accounts Setup
        </x-navigation.dropdown-link>

        <x-navigation.dropdown-link href="{{ route('setup.stores') }}" icon="🏪">
            Stores Setup
        </x-navigation.dropdown-link>
    </x-navigation.dropdown>
@endif

<!-- Portal Navigation -->
<x-navigation.portal-desktop-menu />

<!-- Demo Components -->
@if (request()->routeIs('demo.*'))
    <x-navigation.link href="{{ route('demo.inventory-components') }}" :active="request()->routeIs('demo.inventory-components')" icon="🎨">
        Components Demo
    </x-navigation.link>
@endif
