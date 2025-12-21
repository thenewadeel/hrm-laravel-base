@php
    $currentRoute = request()->route() ? request()->route()->getName() : 'dashboard';
    $currentModule = explode('.', $currentRoute)[0] ?? 'dashboard';
@endphp

<div class="p-4 space-y-6" x-data="{ 
    search: '',
    expandedSections: {
        inventory: {{ $currentModule === 'inventory' ? 'true' : 'false' }},
        accounting: {{ in_array($currentModule, ['accounts', 'accounting']) ? 'true' : 'false' }},
        reports: {{ str_contains($currentRoute, 'reports') ? 'true' : 'false' }},
        hr: {{ in_array($currentModule, ['hr', 'hrm', 'attendance', 'payroll']) ? 'true' : 'false' }},
        organization: {{ $currentModule === 'organization' ? 'true' : 'false' }},
        membership: {{ in_array($currentModule, ['members', 'membership', 'cards', 'fees', 'subscriptions']) ? 'true' : 'false' }},
        admin: {{ $currentModule === 'admin' ? 'true' : 'false' }},
        setup: {{ $currentModule === 'setup' ? 'true' : 'false' }}
    },
    toggleSection(section) {
        this.expandedSections[section] = !this.expandedSections[section];
    }
}">
    {{-- Quick Search --}}
    <div class="relative">
        <input type="text" 
               placeholder="Quick search..." 
               class="w-full px-3 py-2 text-sm border border-secondary rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
               x-model="search">
        <svg class="absolute right-3 top-2.5 w-4 h-4 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </div>

    {{-- Main Navigation --}}
    <nav class="space-y-2">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" 
           class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('dashboard') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
            <span class="mr-3">🏠</span>
            Dashboard
        </a>

        {{-- Inventory Management --}}
        <div class="nav-section">
            <button @click="toggleSection('inventory')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ in_array($currentModule, ['inventory']) ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">📦</span>
                Inventory
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.inventory ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.inventory" x-transition class="mt-1 space-y-1">
                <div class="pl-6 pr-3 py-1">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Inventory Management</div>
                </div>
                <a href="{{ route('inventory.items.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.items.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📦</span>
                    Items
                </a>
                <a href="{{ route('inventory.stores.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.stores.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏪</span>
                    Stores
                </a>
                <a href="{{ route('inventory.transactions.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.transactions.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📋</span>
                    Transactions
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Stock Management</div>
                </div>
                <a href="{{ route('inventory.stock.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.stock.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📊</span>
                    Stock Dashboard
                </a>
                <a href="{{ route('inventory.stock.adjustment') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.stock.adjustment') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">⚖️</span>
                    Stock Adjustment
                </a>
                <a href="{{ route('inventory.stock.count') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.stock.count') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🔢</span>
                    Stock Count
                </a>
                <a href="{{ route('inventory.stock.transfer') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.stock.transfer') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🔄</span>
                    Stock Transfer
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Reports</div>
                </div>
                <a href="{{ route('inventory.reports.stock-levels') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.reports.stock-levels') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📊</span>
                    Stock Levels
                </a>
                <a href="{{ route('inventory.reports.low-stock') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.reports.low-stock') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">⚠️</span>
                    Low Stock Report
                </a>
                <a href="{{ route('inventory.reports.movement') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.reports.movement') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📈</span>
                    Stock Movement
                </a>
            </div>
        </div>

        {{-- Financial Management --}}
        <div class="nav-section">
            <button @click="toggleSection('accounting')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ in_array($currentModule, ['accounts', 'accounting']) ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">💰</span>
                Accounting
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.accounting ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.accounting" x-transition class="mt-1 space-y-1">
                <div class="pl-6 pr-3 py-1">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Core Accounting</div>
                </div>
                <a href="{{ route('accounts.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounts.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📊</span>
                    Chart of Accounts
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Vouchers</div>
                </div>
                <a href="{{ route('accounting.vouchers.sales.create') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.vouchers.sales.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🧾</span>
                    Sales Voucher
                </a>
                <a href="{{ route('accounting.vouchers.purchase.create') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.vouchers.purchase.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🛒</span>
                    Purchase Voucher
                </a>
                <a href="{{ route('accounting.vouchers.expense.create') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.vouchers.expense.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💸</span>
                    Expense Voucher
                </a>
                <a href="{{ route('accounting.vouchers.salary.create') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.vouchers.salary.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💰</span>
                    Salary Voucher
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Bank Management</div>
                </div>
                <a href="{{ route('accounting.bank-accounts.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.bank-accounts.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏦</span>
                    Bank Accounts
                </a>
                <a href="{{ route('accounting.bank-statements.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.bank-statements.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📄</span>
                    Bank Statements
                </a>
                <a href="{{ route('accounting.bank-reconciliation.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.bank-reconciliation.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🔄</span>
                    Bank Reconciliation
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Cash Management</div>
                </div>
                <a href="{{ route('accounting.cash-receipts.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.cash-receipts.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💵</span>
                    Cash Receipts
                </a>
                <a href="{{ route('accounting.cash-payments.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.cash-payments.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💸</span>
                    Cash Payments
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Outstanding Statements</div>
                </div>
                <a href="{{ route('accounting.outstanding.receivables') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.outstanding.receivables') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📈</span>
                    Receivables Outstanding
                </a>
                <a href="{{ route('accounting.outstanding.payables') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.outstanding.payables') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📉</span>
                    Payables Outstanding
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Advanced Accounting</div>
                </div>
                <a href="{{ route('accounting.fixed-assets.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.fixed-assets.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏢</span>
                    Fixed Assets
                </a>
                <a href="{{ route('accounting.financial-years.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.financial-years.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📅</span>
                    Financial Years
                </a>
                <a href="{{ route('accounting.tax.reporting.dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('accounting.tax.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🧾</span>
                    Tax Management
                </a>
            </div>
        </div>

        {{-- Reports & Analytics --}}
        <div class="nav-section">
            <button @click="toggleSection('reports')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ str_contains($currentRoute, 'reports') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">📊</span>
                Reports & Analytics
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.reports ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.reports" x-transition class="mt-1 space-y-1">
                <div class="pl-6 pr-3 py-1">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Financial Reports</div>
                </div>
                <a href="#" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📊</span>
                    Balance Sheet
                </a>
                <a href="#" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📈</span>
                    Income Statement
                </a>
                <a href="#" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">⚖️</span>
                    Trial Balance
                </a>
                <a href="{{ route('accounting.download.trial-balance') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📄</span>
                    Download Trial Balance
                </a>
                <a href="{{ route('accounting.download.balance-sheet') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📄</span>
                    Download Balance Sheet
                </a>
                <a href="{{ route('accounting.download.income-statement') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📄</span>
                    Download Income Statement
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Inventory Reports</div>
                </div>
                <a href="{{ route('inventory.reports.stock-levels') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.reports.stock-levels') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📦</span>
                    Stock Levels Report
                </a>
                <a href="{{ route('inventory.reports.low-stock') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.reports.low-stock') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">⚠️</span>
                    Low Stock Report
                </a>
                <a href="{{ route('inventory.reports.movement') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('inventory.reports.movement') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🔄</span>
                    Stock Movement Report
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">HR & Payroll Reports</div>
                </div>
                <a href="{{ route('attendance.export-payroll') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📊</span>
                    Attendance Export
                </a>
                <a href="{{ route('payroll.advance-reports') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">💵</span>
                    Advance Reports
                </a>
                <a href="{{ route('payroll.report') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📈</span>
                    Payroll Reports
                </a>
            </div>
        </div>

        {{-- Human Resources --}}
        <div class="nav-section">
            <button @click="toggleSection('hr')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ in_array($currentModule, ['hr', 'hrm', 'attendance', 'payroll']) ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">👥</span>
                Human Resources
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.hr ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.hr" x-transition class="mt-1 space-y-1">
                <div class="pl-6 pr-3 py-1">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Employee Management</div>
                </div>
                <a href="{{ route('hr.employees.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('hr.employees.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">👥</span>
                    Employees
                </a>
                <a href="{{ route('hr.positions.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('hr.positions.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💼</span>
                    Job Positions
                </a>
                <a href="{{ route('hr.shifts.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('hr.shifts.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">⏰</span>
                    Shifts
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Attendance Management</div>
                </div>
                <a href="{{ route('attendance.dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('attendance.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">⏱️</span>
                    Attendance Dashboard
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Payroll Management</div>
                </div>
                <a href="{{ route('payroll.dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('payroll.dashboard') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💼</span>
                    Payroll Dashboard
                </a>
                <a href="{{ route('payroll.processing') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('payroll.processing') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">⚙️</span>
                    Payroll Processing
                </a>
                <a href="{{ route('payroll.advances') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('payroll.advances') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💵</span>
                    Salary Advances
                </a>
                <a href="{{ route('payroll.loans') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('payroll.loans') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏦</span>
                    Employee Loans
                </a>
                <a href="{{ route('payroll.increments') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('payroll.increments') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📈</span>
                    Employee Increments
                </a>
                <a href="{{ route('payroll.tax') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('payroll.tax') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🧾</span>
                    Payroll Tax
                </a>
            </div>
        </div>

        {{-- Organization Management --}}
        <div class="nav-section">
            <button @click="toggleSection('organization')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $currentModule === 'organization' ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">🏢</span>
                Organization
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.organization ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.organization" x-transition class="mt-1 space-y-1">
                <a href="{{ route('organization.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('organization.index') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏢</span>
                    Organizations
                </a>
                <a href="{{ route('organization.dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('organization.dashboard') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📊</span>
                    Organization Dashboard
                </a>
                <a href="{{ route('organization.analytics') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('organization.analytics') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📈</span>
                    Analytics
                </a>
                <a href="{{ route('organization.structure') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('organization.structure') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏗️</span>
                    Organization Structure
                </a>
            </div>
        </div>

        {{-- Membership System --}}
        <div class="nav-section">
            <button @click="toggleSection('membership')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ in_array($currentModule, ['members', 'membership', 'cards', 'fees', 'subscriptions']) ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">👥</span>
                Membership
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.membership ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.membership" x-transition class="mt-1 space-y-1">
                <div class="pl-6 pr-3 py-1">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Member Management</div>
                </div>
                <a href="{{ route('members.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('members.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">👥</span>
                    Members
                </a>
                <a href="{{ route('membership.dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('membership.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏠</span>
                    Membership Dashboard
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Card Management</div>
                </div>
                <a href="{{ route('cards.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('cards.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🆔</span>
                    Membership Cards
                </a>
                <a href="{{ route('cards.templates') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">🎨</span>
                    Card Templates
                </a>
                <a href="{{ route('cards.settings') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">⚙️</span>
                    Card Settings
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Fee Management</div>
                </div>
                <a href="{{ route('fees.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('fees.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">💰</span>
                    Fees
                </a>
                <a href="{{ route('fees.statistics') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📊</span>
                    Fee Statistics
                </a>
                
                <div class="pl-6 pr-3 py-1 mt-2">
                    <div class="text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Subscriptions</div>
                </div>
                <a href="{{ route('subscriptions.index') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('subscriptions.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🔄</span>
                    Subscriptions
                </a>
                <a href="{{ route('subscriptions.statistics') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">📈</span>
                    Subscription Statistics
                </a>
                <a href="{{ route('subscriptions.expiring') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">⚠️</span>
                    Expiring Soon
                </a>
            </div>
        </div>

        {{-- Admin Portal --}}
        @if (auth()->check() && auth()->user()->hasRole('admin'))
        <div class="nav-section">
            <button @click="toggleSection('admin')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $currentModule === 'admin' ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">🔧</span>
                Admin Portal
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.admin ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.admin" x-transition class="mt-1 space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('admin.*') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📊</span>
                    Admin Dashboard
                </a>
                <a href="{{ route('admin.attach-user') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">👥</span>
                    Attach User
                </a>
                <a href="{{ route('admin.detach-user') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">🔗</span>
                    Detach User
                </a>
                <a href="#" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors text-secondary hover:bg-secondary hover:text-primary pl-6">
                    <span class="mr-3">🔧</span>
                    Fix User Organization
                </a>
            </div>
        </div>
        @endif

        {{-- System Setup --}}
        @if (auth()->check() && auth()->user()->hasRole(['admin', 'manager']))
        <div class="nav-section">
            <button @click="toggleSection('setup')" 
                    class="w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ $currentModule === 'setup' ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
                <span class="mr-3">⚙️</span>
                System Setup
                <svg class="ml-auto w-4 h-4 transition-transform" 
                     :class="expandedSections.setup ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            
            <div x-show="expandedSections.setup" x-transition class="mt-1 space-y-1">
                <a href="{{ route('setup.organization') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('setup.organization') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏢</span>
                    Organization Setup
                </a>
                <a href="{{ route('setup.accounts') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('setup.accounts') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">📊</span>
                    Accounts Setup
                </a>
                <a href="{{ route('setup.stores') }}" class="nav-item flex items-center px-3 py-2 text-sm rounded-md transition-colors {{ request()->routeIs('setup.stores') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }} pl-6">
                    <span class="mr-3">🏪</span>
                    Stores Setup
                </a>
            </div>
        </div>
        @endif

        {{-- Demo Components --}}
        @if (request()->routeIs('demo.*'))
        <a href="{{ route('demo.inventory-components') }}" 
           class="nav-item flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors {{ request()->routeIs('demo.inventory-components') ? 'bg-primary text-primary-contrast' : 'text-secondary hover:bg-secondary hover:text-primary' }}">
            <span class="mr-3">🎨</span>
            Components Demo
        </a>
        @endif
    </nav>

    {{-- Quick Actions --}}
    <div class="border-t border-secondary pt-4">
        <h3 class="px-3 text-xs font-semibold text-secondary uppercase tracking-wider mb-2">Quick Actions</h3>
        <div class="space-y-2">
            <button class="w-full flex items-center px-3 py-2 text-sm text-secondary hover:bg-secondary hover:text-primary rounded-md transition-colors">
                <span class="mr-2">➕</span>
                New Transaction
            </button>
            <button class="w-full flex items-center px-3 py-2 text-sm text-secondary hover:bg-secondary hover:text-primary rounded-md transition-colors">
                <span class="mr-2">📊</span>
                Generate Report
            </button>
        </div>
    </div>
</div>