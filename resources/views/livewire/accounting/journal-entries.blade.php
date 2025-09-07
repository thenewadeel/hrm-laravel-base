<div class="p-6 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-gray-100">Journal Entries</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Record and manage your company's day-to-day financial
            transactions. Each entry must have at least two accounts with a balanced sum of debits and credits to ensure
            accuracy.</p> <!-- Create Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8 border border-gray-200 dark:border-gray-700">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">Create New Entry</h2>
            <form wire:submit.prevent="createEntry" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of
                            Entry</label>
                        <input type="date" wire:model.defer="entry_date" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('entry_date')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <input type="text" wire:model.defer="description" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="e.g., Payment for office supplies">
                        @error('description')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Transaction Lines</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Add a debit and a credit line for each
                        transaction. Ensure the totals at the bottom are balanced before creating the entry.</p>
                    @error('transactions')
                        <span class="text-red-500 text-xs block mb-4">{{ $message }}</span>
                    @enderror
                    @foreach ($transactions as $index => $transaction)
                        <div class="grid grid-cols-5 gap-4 items-center">
                            <div class="col-span-2">
                                <label for="account-{{ $index }}" class="sr-only">Account</label>
                                <select wire:model.defer="transactions.{{ $index }}.account_id" required
                                    class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Account</option>
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error("transactions.{{ $index }}.account_id")
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="debit-{{ $index }}" class="sr-only">Debit</label>
                                <input type="number" step="0.01"
                                    wire:model.defer="transactions.{{ $index }}.debit"
                                    class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Debit Amount">
                                @error("transactions.{{ $index }}.debit")
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            <div>
                                <label for="credit-{{ $index }}" class="sr-only">Credit</label>
                                <input type="number" step="0.01"
                                    wire:model.defer="transactions.{{ $index }}.credit"
                                    class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Credit Amount">
                                @error("transactions.{{ $index }}.credit")
                                    <span class="text-red-500 text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                            @if (count($transactions) > 2)
                                <button type="button" wire:click="removeTransaction({{ $index }})"
                                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.035 21H7.965a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center">
                    <button type="button" wire:click="addTransaction"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-600 font-medium flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Add New Line</span>
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600">Create
                        Entry</button>
                </div>
            </form>
        </div>

        <!-- Entries Table -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700 overflow-x-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-gray-100">Recent Entries</h2>
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col"
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">
                                Date</th>
                            <th scope="col"
                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                Description</th>
                            <th scope="col"
                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span
                                    class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @forelse ($entries as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <td
                                    class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:pl-6">
                                    {{ $entry->entry_date->format('Y-m-d') }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $entry->description }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($entry->status === 'posted')
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Posted</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Draft</span>
                                    @endif
                                </td>
                                <td
                                    class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-2">
                                    <a href="#"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">View</a>
                                    <a href="#"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <p class="mb-1">No journal entries have been created yet.</p>
                                    <p>Start by creating your first entry using the form above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
