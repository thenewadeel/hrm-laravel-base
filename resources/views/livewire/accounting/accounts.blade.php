<div class="p-6 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">Chart of Accounts</h1>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">All Accounts</h2>
                <button wire:click="$toggle('showForm')"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-150 ease-in-out shadow-md">
                    {{ $showForm ? 'Hide Form' : 'Add New Account' }}
                </button>
            </div>

            <!-- New Account Form -->
            <div x-show="true" x-transition x-data="{ showForm: @entangle('showForm') }" x-cloak>
                <form wire:submit.prevent="createAccount" class="mb-6 space-y-4" x-show="showForm">
                    <div class="flex flex-col md:flex-row gap-4">
                        <input type="text" wire:model.defer="name" placeholder="Account Name" required
                            class="flex-1 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                        <select wire:model.defer="type" required
                            class="flex-1 rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Account Type</option>
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Revenue">Revenue</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-4">
                        <label for="is_active"
                            class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</label>
                        <input type="checkbox" wire:model.defer="is_active" id="is_active"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition duration-150 ease-in-out shadow-md">
                            Save Account
                        </button>
                    </div>
                </form>
            </div>

            <!-- Reusable Accounts Table Partial -->
            @include('partials.accounts-table', ['accounts' => $accounts])
        </div>
    </div>
</div>
