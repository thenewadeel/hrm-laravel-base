<x-layout>{{-- Header Section --}}<div class="bg-gradient-to-r from-gray-50 to-gray-100 py-8 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">🏭 Accounts Management</h1>
            {{-- Tab Navigation for Alpine.js to switch between Livewire components --}}<nav class="flex space-x-4"><a href="#" @click.prevent="activeTab = 'dashboard'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'dashboard' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Dashboard</a><a
                    href="#" @click.prevent="activeTab = 'accounts'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'accounts' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Chart
                    of Accounts</a><a href="#" @click.prevent="activeTab = 'journal-entries'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'journal-entries' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Journal
                    Entries</a><a href="#" @click.prevent="activeTab = 'reports'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'reports' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Financial
                    Reports</a></nav>
        </div>
    </div>{{-- Main Content Area --}}
    <div class="bg-gray-50 min-h-screen py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="{ activeTab: 'dashboard' }" class="p-4 bg-white shadow-lg rounded-xl overflow-hidden mb-8">
                {{-- Tab Navigation --}}
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'dashboard'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'dashboard', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'dashboard' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Dashboard
                        </button>
                        <button type="button" @click="activeTab = 'accounts'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'accounts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'accounts' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Accounts
                        </button>
                        <button type="button" @click="activeTab = 'journal-entries'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'journal-entries', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'journal-entries' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Journal Entries
                        </button>
                        <button type="button" @click="activeTab = 'reports'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'reports', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reports' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Financial Reports
                        </button>
                        <button type="button" @click="activeTab = 'chart-of-accounts'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'chart-of-accounts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'chart-of-accounts' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Chart of Accounts
                        </button>
                    </nav>
                </div>
                {{-- @livewire('api-data-fetcher') --}}
                {{-- Tab Content --}}
                <div class="mt-8 p-4 bg-white shadow-lg rounded-xl overflow-hidden">
                    <div x-show="activeTab === 'dashboard'">
                        @livewire('accounting.dashboard')
                    </div>
                    <div x-show="activeTab === 'accounts'">
                        tbd
                    </div>
                    <div x-show="activeTab === 'journal-entries'">
                        @livewire('accounting.journal-entries')
                    </div>
                    <div x-show="activeTab === 'reports'">
                        {{-- @livewire('accounting.reports') --}}
                    </div>
                    <div x-show="activeTab === 'chart-of-accounts'">
                        @livewire('accounting.chart-of-accounts')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
