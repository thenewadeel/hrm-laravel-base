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
                                        <x-heroicon-o-face-smile class="h-8 w-8 text-green-600" />
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
                                        <x-heroicon-o-shopping-cart class="h-8 w-8 text-red-600" />
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
                                        <x-heroicon-o-check-circle class="h-8 w-8 text-green-600" />
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
                                        <x-heroicon-o-shopping-bag class="h-8 w-8 text-blue-600" />
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
                                        <x-heroicon-o-user-group class="h-8 w-8 text-purple-600" />
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
                                        <x-heroicon-o-document-text class="h-8 w-8 text-red-600" />
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
