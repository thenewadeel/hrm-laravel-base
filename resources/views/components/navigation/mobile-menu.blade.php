{{-- Dashboard --}}
<x-navigation.link 
    href="{{ route('dashboard') }}" 
    :active="request()->routeIs('dashboard')"
    icon="🏠"
>
    Dashboard
</x-navigation.link>

{{-- Inventory Management --}}
<x-navigation.section title="Inventory" icon="📦">
    <x-navigation.mobile-link 
        href="{{ route('inventory.items.index') }}" 
        :active="request()->routeIs('inventory.items.*')"
        icon="📦"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Items
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.stores.index') }}" 
        :active="request()->routeIs('inventory.stores.*')"
        icon="🏪"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Stores
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.transactions.index') }}" 
        :active="request()->routeIs('inventory.transactions.*')"
        icon="📋"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Transactions
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.stock.adjustment') }}" 
        :active="request()->routeIs('inventory.stock.adjustment')"
        icon="⚖️"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Stock Adjustment
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.stock.count') }}" 
        :active="request()->routeIs('inventory.stock.count')"
        icon="🔢"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Stock Count
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.stock.transfer') }}" 
        :active="request()->routeIs('inventory.stock.transfer')"
        icon="🔄"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Stock Transfer
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.reports.stock-levels') }}" 
        :active="request()->routeIs('inventory.reports.stock-levels')"
        icon="📊"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Stock Levels
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.reports.low-stock') }}" 
        :active="request()->routeIs('inventory.reports.low-stock')"
        icon="⚠️"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Low Stock Report
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('inventory.reports.movement') }}" 
        :active="request()->routeIs('inventory.reports.movement')"
        icon="📈"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Stock Movement
    </x-navigation.mobile-link>
</x-navigation.section>

{{-- Financial Management --}}
<x-navigation.section title="Accounting" icon="💰">
    <x-navigation.mobile-link 
        href="{{ route('accounts.index') }}" 
        :active="request()->routeIs('accounts.*')"
        icon="📊"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Chart of Accounts
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.vouchers.sales.create') }}" 
        :active="request()->routeIs('accounting.vouchers.*')"
        icon="🧾"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Sales Voucher
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.vouchers.purchase.create') }}" 
        :active="request()->routeIs('accounting.vouchers.purchase.*')"
        icon="🛒"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Purchase Voucher
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.vouchers.expense.create') }}" 
        :active="request()->routeIs('accounting.vouchers.expense.*')"
        icon="💸"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Expense Voucher
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.vouchers.salary.create') }}" 
        :active="request()->routeIs('accounting.vouchers.salary.*')"
        icon="💰"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Salary Voucher
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.bank-accounts.index') }}" 
        :active="request()->routeIs('accounting.bank-accounts.*')"
        icon="🏦"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Bank Accounts
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.bank-statements.index') }}" 
        :active="request()->routeIs('accounting.bank-statements.*')"
        icon="📄"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Bank Statements
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.bank-reconciliation.index') }}" 
        :active="request()->routeIs('accounting.bank-reconciliation.*')"
        icon="🔄"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Bank Reconciliation
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.cash-receipts.index') }}" 
        :active="request()->routeIs('accounting.cash-receipts.*')"
        icon="💵"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Cash Receipts
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.cash-payments.index') }}" 
        :active="request()->routeIs('accounting.cash-payments.*')"
        icon="💸"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Cash Payments
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.outstanding.receivables') }}" 
        :active="request()->routeIs('accounting.outstanding.receivables')"
        icon="📈"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Receivables Outstanding
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.outstanding.payables') }}" 
        :active="request()->routeIs('accounting.outstanding.payables')"
        icon="📉"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Payables Outstanding
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.fixed-assets.index') }}" 
        :active="request()->routeIs('accounting.fixed-assets.*')"
        icon="🏢"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Fixed Assets
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.financial-years.index') }}" 
        :active="request()->routeIs('accounting.financial-years.*')"
        icon="📅"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Financial Years
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('accounting.tax.reporting.dashboard') }}" 
        :active="request()->routeIs('accounting.tax.*')"
        icon="🧾"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Tax Management
    </x-navigation.mobile-link>
</x-navigation.section>

{{-- Human Resources --}}
<x-navigation.section title="Human Resources" icon="👥">
    <x-navigation.mobile-link 
        href="{{ route('hr.employees.index') }}" 
        :active="request()->routeIs('hr.employees.*')"
        icon="👥"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Employees
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('hr.positions.index') }}" 
        :active="request()->routeIs('hr.positions.*')"
        icon="💼"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Job Positions
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('hr.shifts.index') }}" 
        :active="request()->routeIs('hr.shifts.*')"
        icon="⏰"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Shifts
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('attendance.dashboard') }}" 
        :active="request()->routeIs('attendance.*')"
        icon="⏱️"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Attendance Dashboard
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.dashboard') }}" 
        :active="request()->routeIs('payroll.dashboard')"
        icon="💼"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Payroll Dashboard
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.processing') }}" 
        :active="request()->routeIs('payroll.processing')"
        icon="⚙️"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Payroll Processing
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.advances') }}" 
        :active="request()->routeIs('payroll.advances')"
        icon="💵"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Salary Advances
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.loans') }}" 
        :active="request()->routeIs('payroll.loans')"
        icon="🏦"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Employee Loans
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.increments') }}" 
        :active="request()->routeIs('payroll.increments')"
        icon="📈"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Employee Increments
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.tax') }}" 
        :active="request()->routeIs('payroll.tax')"
        icon="🧾"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Payroll Tax
    </x-navigation.mobile-link>
</x-navigation.section>

{{-- Organization Management --}}
<x-navigation.mobile-link 
    href="{{ route('organization.index') }}" 
    :active="request()->routeIs('organization.*')"
    icon="🏢"
    @click="$wire.dispatch('close-mobile-menu')"
>
    Organizations
</x-navigation.mobile-link>

<x-navigation.mobile-link 
    href="{{ route('organization.dashboard') }}" 
    :active="request()->routeIs('organization.dashboard')"
    icon="📊"
    @click="$wire.dispatch('close-mobile-menu')"
>
    Organization Dashboard
</x-navigation.mobile-link>

<x-navigation.mobile-link 
    href="{{ route('organization.analytics') }}" 
    :active="request()->routeIs('organization.analytics')"
    icon="📈"
    @click="$wire.dispatch('close-mobile-menu')"
>
    Analytics
</x-navigation.mobile-link>

{{-- Membership System --}}
<x-navigation.section title="Membership" icon="👥">
    <x-navigation.mobile-link 
        href="{{ route('members.index') }}" 
        :active="request()->routeIs('members.*')"
        icon="👥"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Members
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('membership.dashboard') }}" 
        :active="request()->routeIs('membership.*')"
        icon="🏠"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Membership Dashboard
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('cards.index') }}" 
        :active="request()->routeIs('cards.*')"
        icon="🆔"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Membership Cards
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('fees.index') }}" 
        :active="request()->routeIs('fees.*')"
        icon="💰"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Fees
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('subscriptions.index') }}" 
        :active="request()->routeIs('subscriptions.*')"
        icon="🔄"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Subscriptions
    </x-navigation.mobile-link>
</x-navigation.section>

{{-- Admin Portal --}}
@if (auth()->check() && auth()->user()->hasRole('admin'))
    <x-navigation.section title="Admin Portal" icon="🔧">
        <x-navigation.mobile-link 
            href="{{ route('admin.dashboard') }}" 
            :active="request()->routeIs('admin.*')"
            icon="📊"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Admin Dashboard
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('admin.attach-user') }}" 
            :active="request()->routeIs('admin.attach-user')"
            icon="👥"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Attach User
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('admin.detach-user') }}" 
            :active="request()->routeIs('admin.detach-user')"
            icon="🔗"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Detach User
        </x-navigation.mobile-link>
    </x-navigation.section>
@endif

{{-- System Setup --}}
@if (auth()->check() && auth()->user()->hasRole(['admin', 'manager']))
    <x-navigation.section title="System Setup" icon="⚙️">
        <x-navigation.mobile-link 
            href="{{ route('setup.organization') }}" 
            :active="request()->routeIs('setup.organization')"
            icon="🏢"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Organization Setup
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('setup.accounts') }}" 
            :active="request()->routeIs('setup.accounts')"
            icon="📊"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Accounts Setup
        </x-navigation.mobile-link>
        
        <x-navigation.mobile-link 
            href="{{ route('setup.stores') }}" 
            :active="request()->routeIs('setup.stores')"
            icon="🏪"
            @click="$wire.dispatch('close-mobile-menu')"
        >
            Stores Setup
        </x-navigation.mobile-link>
    </x-navigation.section>
@endif

{{-- Demo Components --}}
@if (request()->routeIs('demo.*'))
    <x-navigation.mobile-link 
        href="{{ route('demo.inventory-components') }}" 
        :active="request()->routeIs('demo.inventory-components')"
        icon="🎨"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Components Demo
    </x-navigation.mobile-link>
@endif