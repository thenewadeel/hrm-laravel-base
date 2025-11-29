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
        href="{{ route('inventory.reports.stock-levels') }}" 
        :active="request()->routeIs('inventory.reports.*')"
        icon="📊"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Reports
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
        Vouchers
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
        href="{{ route('accounting.bank-accounts.index') }}" 
        :active="request()->routeIs('accounting.bank-accounts.*')"
        icon="🏦"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Bank Accounts
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
        Attendance
    </x-navigation.mobile-link>
    
    <x-navigation.mobile-link 
        href="{{ route('payroll.dashboard') }}" 
        :active="request()->routeIs('payroll.*')"
        icon="💼"
        @click="$wire.dispatch('close-mobile-menu')"
    >
        Payroll
    </x-navigation.mobile-link>
</x-navigation.section>

{{-- Organization Management --}}
<x-navigation.mobile-link 
    href="{{ route('organizations.index') }}" 
    :active="request()->routeIs('organizations.*')"
    icon="🏢"
    @click="$wire.dispatch('close-mobile-menu')"
>
    Organizations
</x-navigation.mobile-link>

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