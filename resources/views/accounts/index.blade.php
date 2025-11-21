<x-layout>{{-- Header Section --}}<div class="bg-gradient-to-r from-gray-50 to-gray-100 py-8 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">🏭 Accounts Management</h1>
            {{-- Tab Navigation for Alpine.js to switch between Livewire components<nav class="flex space-x-4"><a href="#" @click.prevent="activeTab = 'dashboard'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'dashboard' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Dashboard</a><a
                    href="#" @click.prevent="activeTab = 'accounts'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'accounts' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Chart
                    of Accounts</a><a href="#" @click.prevent="activeTab = 'journal-entries'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'journal-entries' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Journal
                    Entries</a><a href="#" @click.prevent="activeTab = 'reports'"
                    :class="{ 'bg-gray-200 text-gray-800': activeTab === 'reports' }"class="px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">Financial
                    Reports</a></nav> --}}
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
                        <button type="button" @click="activeTab = 'vouchers'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'vouchers', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'vouchers' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Vouchers
                        </button>
                        <button type="button" @click="activeTab = 'chart-of-accounts'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'chart-of-accounts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'chart-of-accounts' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Chart of Accounts
                        </button>
                        {{-- <button type="button" @click="activeTab = 'accounts'"
                            :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'accounts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'accounts' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Accounts
                        </button> --}}
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
                    </nav>
                </div>
                {{-- @livewire('api-data-fetcher') --}}
                {{-- Tab Content --}}
                <div class="mt-8 p-4 bg-white shadow-lg rounded-xl overflow-hidden">
                    <div x-show="activeTab === 'dashboard'">
                        @livewire('accounting.dashboard')
                    </div>
                    <div x-show="activeTab === 'accounts'">
                        @foreach ($accounts as $account)
                            @if ($account->ledgerEntries->count() != 0)
                                {{-- <h1 title="{{ $account->ledgerEntries }}">{{ $account->name }}</h1> --}}
                                @livewire('accounting.ledger-entries', ['entries' => $account->ledgerEntries, 'title' => $account->name])
                            @endif
                        @endforeach

                    </div>
                    <div x-show="activeTab === 'journal-entries'">
                        @livewire('accounting.journal-entries')
                    </div>
                    <div x-show="activeTab === 'reports'">
                        @livewire('accounting.reports')
                    </div>
                    <div x-show="activeTab === 'vouchers'" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="{{ route('accounting.vouchers.sales.create') }}" 
                               class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Sales Voucher</h3>
                                        <p class="text-sm text-gray-500">Create sales invoices</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.vouchers.purchase.create') }}" 
                               class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Purchase Voucher</h3>
                                        <p class="text-sm text-gray-500">Record purchase invoices</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.vouchers.salary.create') }}" 
                               class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Salary Voucher</h3>
                                        <p class="text-sm text-gray-500">Process salary payments</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.vouchers.expense.create') }}" 
                               class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-50 transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Expense Voucher</h3>
                                        <p class="text-sm text-gray-500">Record expenses</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div x-show="activeTab === 'chart-of-accounts'">
                        @livewire('accounting.chart-of-accounts')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
