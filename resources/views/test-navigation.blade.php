<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            👤 {{ __('test nav') }}
        </h2>
    </x-slot>

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Navigation Test Page</h1>
        <x-navigation.examples />
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Navigation Components Test</h2>

            <div class="space-y-6">
                <!-- Test Basic Navigation Link -->
                <div>
                    <h3 class="text-md font-medium mb-2">Basic Navigation Link</h3>
                    <x-navigation.link href="{{ route('dashboard') }}" :active="true" icon="🏠">
                        Dashboard Link Test
                    </x-navigation.link>
                </div>

                <!-- Test Dropdown -->
                <div>
                    <h3 class="text-md font-medium mb-2">Dropdown Test</h3>
                    <x-navigation.dropdown>
                        <x-slot name="trigger">
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                Test Dropdown
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-navigation.dropdown-link href="#" icon="📦">
                                Test Item 1
                            </x-navigation.dropdown-link>
                            <x-navigation.dropdown-link href="#" icon="🏪">
                                Test Item 2
                            </x-navigation.dropdown-link>
                        </x-slot>
                    </x-navigation.dropdown>
                </div>

                <!-- Test User Profile -->
                <div>
                    <h3 class="text-md font-medium mb-2">User Profile Test</h3>
                    <x-navigation.user-profile :user="auth()->user()" :showRole="true" :showStatus="true" size="lg" />
                </div>

                <!-- Test Search -->
                <div>
                    <h3 class="text-md font-medium mb-2">Search Test</h3>
                    <x-navigation.search action="{{ route('dashboard') }}" placeholder="Search test..." />
                </div>

                <!-- Test Breadcrumb -->
                <div>
                    <h3 class="text-md font-medium mb-2">Breadcrumb Test</h3>
                    <x-navigation.breadcrumb :pages="[
                        ['title' => 'Home', 'href' => route('dashboard')],
                        ['title' => 'Test', 'href' => '#'],
                        ['title' => 'Current Page'],
                    ]" />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
