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

    <x-navigation.dropdown-link href="{{ route('inventory.items.index') }}" icon="📦">
        Items
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.stores.index') }}" icon="🏪">
        Stores
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('inventory.transactions.index') }}" icon="📋">
        Transactions
    </x-navigation.dropdown-link>

    <div class="border-t border-secondary"></div>

    <x-navigation.dropdown-link href="{{ route('inventory.reports.stock-levels') }}" icon="📊">
        Reports
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

    <x-navigation.dropdown-link href="{{ route('accounting.vouchers.sales.create') }}" icon="🧾">
        Vouchers
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

    <x-navigation.dropdown-link href="{{ route('accounting.bank-accounts.index') }}" icon="🏦">
        Bank Accounts
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

    <x-navigation.dropdown-link href="{{ route('hr.employees.index') }}" icon="👥">
        Employees
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('hr.shifts.index') }}" icon="⏰">
        Shifts
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('attendance.dashboard') }}" icon="⏱️">
        Attendance
    </x-navigation.dropdown-link>

    <x-navigation.dropdown-link href="{{ route('payroll.dashboard') }}" icon="💼">
        Payroll
    </x-navigation.dropdown-link>
</x-navigation.dropdown>

{{-- Organization Management --}}
<x-navigation.link href="{{ route('organization.index') }}" :active="request()->routeIs('organizations.*')" icon="🏢">
    Organizations
</x-navigation.link>

<!-- Portal Navigation -->
<x-navigation.portal-desktop-menu />

<!-- Demo Components -->
@if (request()->routeIs('demo.*'))
    <x-navigation.link href="{{ route('demo.inventory-components') }}" :active="request()->routeIs('demo.inventory-components')" icon="🎨">
        Components Demo
    </x-navigation.link>
@endif
