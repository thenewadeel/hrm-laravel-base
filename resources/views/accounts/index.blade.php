<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            🏭 Accounts Management
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="{ activeTab: 'dashboard' }" class="surface overflow-hidden shadow rounded-lg">
                {{-- Tab Navigation --}}
                <div class="border-b border-secondary">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'dashboard'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'dashboard', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'dashboard' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Dashboard
                        </button>
                        <button type="button" @click="activeTab = 'vouchers'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'vouchers', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'vouchers' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Vouchers
                        </button>
                        <button type="button" @click="activeTab = 'chart-of-accounts'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'chart-of-accounts', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'chart-of-accounts' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Chart of Accounts
                        </button>
                        <button type="button" @click="activeTab = 'journal-entries'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'journal-entries', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'journal-entries' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Journal Entries
                        </button>
                        <button type="button" @click="activeTab = 'outstanding'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'outstanding', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'outstanding' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Outstanding Statements
                        </button>
                        <button type="button" @click="activeTab = 'reports'"
                            :class="{ 'border-blue-500 text-blue-600': activeTab === 'reports', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reports' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Financial Reports
                        </button>
                    </nav>
                </div>

                {{-- Tab Content --}}
                <div class="p-6">
                    <div x-show="activeTab === 'dashboard'" x-transition>
                        @livewire('accounting.dashboard')
                    </div>
                    <div x-show="activeTab === 'accounts'" x-transition>
                        @foreach ($accounts as $account)
                            @if ($account->ledgerEntries->count() != 0)
                                @livewire('accounting.ledger-entries', ['entries' => $account->ledgerEntries, 'title' => $account->name])
                            @endif
                        @endforeach
                    </div>
                    <div x-show="activeTab === 'journal-entries'" x-transition>
                        @livewire('accounting.journal-entries')
                    </div>
                    <div x-show="activeTab === 'outstanding'" class="space-y-6" x-transition>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <a href="{{ route('accounting.outstanding.receivables') }}" 
                               class="block p-6 surface border border-secondary rounded-lg shadow-sm hover:bg-tertiary transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v2m0-4c0 1.11-.89 2-2 2H8m8 0c1.11 0 2-.89 2-2V8m0 0V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-primary">Receivables Outstanding</h3>
                                        <p class="text-sm text-secondary">Customer outstanding balances with aging analysis</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.outstanding.payables') }}" 
                               class="block p-6 surface border border-secondary rounded-lg shadow-sm hover:bg-tertiary transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 002-2v-4a2 2 0 00-2-2H5m0 0a2 2 0 00-2 2v4a2 2 0 002 2h16z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-primary">Payables Outstanding</h3>
                                        <p class="text-sm text-secondary">Vendor outstanding balances with aging analysis</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div x-show="activeTab === 'reports'" x-transition>
                        @livewire('accounting.reports')
                    </div>
                    <div x-show="activeTab === 'vouchers'" class="space-y-6" x-transition>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="{{ route('accounting.vouchers.sales.create') }}" 
                               class="block p-6 surface border border-secondary rounded-lg shadow-sm hover:bg-tertiary transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-primary">Sales Voucher</h3>
                                        <p class="text-sm text-secondary">Create sales invoices</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.vouchers.purchase.create') }}" 
                               class="block p-6 surface border border-secondary rounded-lg shadow-sm hover:bg-tertiary transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-primary">Purchase Voucher</h3>
                                        <p class="text-sm text-secondary">Record purchase invoices</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.vouchers.salary.create') }}" 
                               class="block p-6 surface border border-secondary rounded-lg shadow-sm hover:bg-tertiary transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-primary">Salary Voucher</h3>
                                        <p class="text-sm text-secondary">Process salary payments</p>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="{{ route('accounting.vouchers.expense.create') }}" 
                               class="block p-6 surface border border-secondary rounded-lg shadow-sm hover:bg-tertiary transition-colors">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-primary">Expense Voucher</h3>
                                        <p class="text-sm text-secondary">Record expenses</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div x-show="activeTab === 'chart-of-accounts'" x-transition>
                        @livewire('accounting.chart-of-accounts')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
