<div x-data="{ activeTab: @entangle('activeTab') }" class="p-4">
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <a href="#" wire:click.prevent="activeTab = 'trial-balance'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'trial-balance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'trial-balance' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">Trial
                Balance</a>
            <a href="#" wire:click.prevent="activeTab = 'balance-sheet'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'balance-sheet', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'balance-sheet' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">Balance
                Sheet</a>
            <a href="#" wire:click.prevent="activeTab = 'income-statement'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'income-statement', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'income-statement' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200">Income
                Statement</a>
        </nav>
    </div>
    <div class="mt-8 p-4 bg-white shadow-lg rounded-xl overflow-hidden">
        <div x-show="activeTab === 'trial-balance'" class="p-4">
            {{-- @include('partials.accounting.trial-balance') --}}
            @include('partials.accounting.trial-balance', ['report' => $trialBalanceReport])
        </div>
        <div x-show="activeTab === 'balance-sheet'" class="p-4">
            {{-- @include('partials.accounting.balance-sheet') --}}
            @include('partials.accounting.balance-sheet', ['report' => $balanceSheetReport])
        </div>
        <div x-show="activeTab === 'income-statement'" class="p-4">
            {{-- @include('partials.accounting.income-statement') --}}
            @include('partials.accounting.income-statement', ['report' => $incomeStatementReport])
        </div>
    </div>
</div>
