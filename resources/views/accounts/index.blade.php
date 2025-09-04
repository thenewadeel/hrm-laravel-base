{{-- resources/views/organizations/index.blade.php --}}<x-layout>{{-- Header Section --}}<div
        class="bg-gradient-to-r from-gray-50 to-gray-100 py-8 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight mb-4 sm:mb-0">🏭 Accounts Management</h1>
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
    </div>{{-- Main Content Area with conditional component rendering --}}<div class="bg-gray-50 dark:bg-gray-900 min-h-screen py-10" x-data="{ activeTab: 'dashboard' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">{{-- Dashboard Component --}}<div x-show="activeTab === 'dashboard'"
                class="w-full" x-cloak>@livewire('accounting.dashboard')</div>{{-- Chart of Accounts Component --}}
            <div x-show="activeTab === 'accounts'" class="w-full" x-cloak>
                @livewire('accounting.accounts')
            </div>

            {{-- Journal Entries Component --}}
            <div x-show="activeTab === 'journal-entries'" class="w-full" x-cloak>
                @livewire('accounting.journal-entries')
            </div>

            {{-- Financial Reports Component --}}
            <div x-show="activeTab === 'reports'" class="w-full" x-cloak>
                {{-- @livewire('accounting.reports') --}}
            </div>
        </div>
    </div></x-layout>
