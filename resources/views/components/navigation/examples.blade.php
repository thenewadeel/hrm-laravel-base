{{-- Navigation Usage Examples --}}

{{-- Basic Navigation --}}
<x-navigation.main />

{{-- Sidebar Layout --}}
<x-navigation.sidebar-layout>
    <div class="p-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Welcome to your dashboard</p>
    </div>
</x-navigation.sidebar-layout>

{{-- Individual Components --}}
{{-- Navigation Links --}}
<x-navigation.link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="🏠">
    Dashboard
</x-navigation.link>

<!-- Dropdown Navigation -->
<x-navigation.dropdown>
    <x-slot name="trigger">
        <button
            class="flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
            Inventory
            <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        <x-navigation.dropdown-link href="{{ route('inventory.items.index') }}" icon="📦">
            Items
        </x-navigation.dropdown-link>
        <x-navigation.dropdown-link href="{{ route('inventory.stores.index') }}" icon="🏪">
            Stores
        </x-navigation.dropdown-link>
    </x-slot>
</x-navigation.dropdown>

<!-- User Profile Widget -->
<x-navigation.user-profile :user="auth()->user()" :showRole="true" :showStatus="true" size="lg" />

<!-- Sidebar Widget -->
<x-navigation.sidebar-widget title="Quick Stats" :items="[
    ['label' => 'Total Sales', 'value' => '$12,345', 'description' => 'This month'],
    ['label' => 'New Orders', 'value' => '45', 'description' => 'Today'],
    ['label' => 'Pending', 'value' => '8', 'description' => 'Awaiting action'],
]" />

<!-- Stats Widget with Progress -->
<x-navigation.sidebar-widget title="Monthly Progress" icon="📊" color="green" :progress="75" :trend="'up'"
    trendValue="+12%" description="Sales increased by 12% compared to last month">
    <div class="text-2xl font-bold">$45,678</div>
</x-navigation.sidebar-widget>

<!-- Quick Actions -->
<x-navigation.quick-actions />

<!-- Search Bar -->
<x-navigation.search placeholder="Search products, customers, orders..." />

<!-- Breadcrumb Navigation -->
<x-navigation.breadcrumb :pages="[
    ['title' => 'Home', 'href' => route('dashboard')],
    ['title' => 'Inventory', 'href' => route('inventory.items.index')],
    ['title' => 'Items', 'href' => '#'],
]" />

<!-- Notification Bell -->
<x-navigation.notification-bell :count="5" :showCount="true" />

<!-- Theme Toggle -->
<x-navigation.theme-toggle :dark-mode="true" />

<!-- Language Switcher -->
<x-navigation.language-switcher :currentLocale="'en'" :availableLocales="[
    'en' => 'English',
    'es' => 'Español',
    'fr' => 'Français',
]" />
