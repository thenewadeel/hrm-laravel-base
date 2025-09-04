<div x-data="{ activeTab: 'trial-balance' }" class="p-4">{{-- Tab Navigation --}}<div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs"><a href="#" @click.prevent="activeTab = 'trial-balance'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'trial-balance', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'trial-balance' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Trial Balance</a><a href="#"
                @click.prevent="activeTab = 'balance-sheet'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'balance-sheet', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'balance-sheet' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Balance Sheet</a><a href="#"
                @click.prevent="activeTab = 'income-statement'"
                :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'income-statement', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'income-statement' }"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Income Statement</a></nav>
    </div>{{-- Tab Content --}}
    <div class="mt-8 p-4 bg-white shadow-lg rounded-xl overflow-hidden">
        <div x-show="activeTab === 'trial-balance'" class="p-4">
            @include('partials.trial-bakance')
        </div>
        <div x-show="activeTab === 'balance-sheet'" class="p-4">
            {{-- @include('livewire.accounting.reports._balance-sheet') --}}
            <p>Balance Sheet content goes here.</p>
        </div>
        <div x-show="activeTab === 'income-statement'" class="p-4">
            {{-- @include('livewire.accounting.reports._income-statement') --}}
            <p>Income Statement content goes here.</p>
        </div>
    </div>
</div>
