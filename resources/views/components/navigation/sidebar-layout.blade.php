{{-- Enhanced Navigation with Sidebar --}}
<div class="flex h-screen bg-gray-100 dark:bg-gray-900">
    <!-- Sidebar -->
    <aside class="hidden md:flex md:flex-shrink-0 md:w-64">
        <div class="flex flex-col h-full">
            <!-- Logo Area -->
            <div class="flex items-center h-16 px-4 bg-indigo-600 dark:bg-indigo-800">
                <a href="{{ route('dashboard') }}">
                    <x-application-mark class="h-8 w-auto text-white" />
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <!-- Search -->
                {{-- TODO: <x-navigation.search action="{{ route('search') }}" placeholder="Search anything..." /> --}}

                <!-- Main Navigation -->
                <div class="space-y-1">
                    <x-navigation.link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="🏠">
                        Dashboard
                    </x-navigation.link>

                    <!-- Inventory Section -->
                    <x-navigation.section title="Inventory" icon="📦">
                        <x-navigation.link href="{{ route('inventory.items.index') }}" :active="request()->routeIs('inventory.items.*')" icon="📦">
                            Items
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('inventory.stores.index') }}" :active="request()->routeIs('inventory.stores.*')"
                            icon="🏪">
                            Stores
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('inventory.transactions.index') }}" :active="request()->routeIs('inventory.transactions.*')"
                            icon="📋">
                            Transactions
                        </x-navigation.link>
                    </x-navigation.section>

                    <!-- Accounting Section -->
                    <x-navigation.section title="Accounting" icon="💰">
                        <x-navigation.link href="{{ route('accounts.index') }}" :active="request()->routeIs('accounts.*')" icon="📊">
                            Chart of Accounts
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('accounting.vouchers.sales.create') }}" :active="request()->routeIs('accounting.vouchers.*')"
                            icon="🧾">
                            Vouchers
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('accounting.cash-receipts.index') }}" :active="request()->routeIs('accounting.cash-receipts.*')"
                            icon="💵">
                            Cash Receipts
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('accounting.cash-payments.index') }}" :active="request()->routeIs('accounting.cash-payments.*')"
                            icon="💸">
                            Cash Payments
                        </x-navigation.link>
                    </x-navigation.section>

                    <!-- HR Section -->
                    <x-navigation.section title="Human Resources" icon="👥">
                        <x-navigation.link href="{{ route('hr.employees.index') }}" :active="request()->routeIs('hr.employees.*')" icon="👥">
                            Employees
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('hr.shifts.index') }}" :active="request()->routeIs('hr.shifts.*')" icon="⏰">
                            Shifts
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('attendance.dashboard') }}" :active="request()->routeIs('attendance.*')"
                            icon="⏱️">
                            Attendance
                        </x-navigation.link>

                        <x-navigation.link href="{{ route('payroll.dashboard') }}" :active="request()->routeIs('payroll.*')" icon="💼">
                            Payroll
                        </x-navigation.link>
                    </x-navigation.section>

                    <!-- Organization -->
                    <x-navigation.link href="{{ route('organizations.index') }}" :active="request()->routeIs('organizations.*')" icon="🏢">
                        Organizations
                    </x-navigation.link>
                </div>
            </nav>

            <!-- User Profile -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <x-navigation.user-profile :user="auth()->user()" />
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex flex-col md:pl-64">
        <!-- Top Bar -->
        <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-4">
                    <!-- Breadcrumbs -->
                    <x-navigation.breadcrumb :pages="$breadcrumbs ?? []" />

                    <!-- Notifications -->
                    <x-navigation.notification-bell :count="$notificationCount ?? 0" />

                    <!-- Language Switcher -->
                    <x-navigation.language-switcher :currentLocale="$currentLocale ?? 'en'" :availableLocales="$availableLocales ?? ['en' => 'English']" />

                    <!-- Theme Toggle -->
                    <x-navigation.theme-toggle :dark-mode="$darkMode ?? false" />
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-auto">
            {{ $slot }}
        </main>
    </div>
</div>
