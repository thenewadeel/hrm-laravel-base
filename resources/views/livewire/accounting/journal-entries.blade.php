<div class="p-6 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">Journal Entries</h1>
        <!-- Create Journal Entry Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">Create New Entry</h2>
            <form wire:submit.prevent="createEntry" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input type="date" wire:model.defer="date" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                        <input type="text" wire:model.defer="description" placeholder="e.g., Office Rent" required
                            class="mt-1 block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <!-- Transaction Rows -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Transactions</h3>
                    @foreach ($transactions as $index => $transaction)
                        <div class="grid grid-cols-5 gap-4 items-center">
                            <div class="col-span-2">
                                <label class="sr-only">Account</label>
                                <select wire:model.defer="transactions.{{ $index }}.account" required
                                    class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Account</option>
                                    <!-- Options would be dynamically loaded from the accounts list -->
                                    <option value="Cash">Cash</option>
                                    <option value="Sales Revenue">Sales Revenue</option>
                                    <option value="Rent Expense">Rent Expense</option>
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="sr-only">Debit</label>
                                <input type="number" wire:model.defer="transactions.{{ $index }}.debit"
                                    placeholder="Debit"
                                    class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="col-span-1">
                                <label class="sr-only">Credit</label>
                                <input type="number" wire:model.defer="transactions.{{ $index }}.credit"
                                    placeholder="Credit"
                                    class="block w-full rounded-md border-gray-300 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div class="col-span-1 flex justify-end">
                                <button type="button" wire:click="removeTransaction({{ $index }})"
                                    class="p-2 text-red-500 hover:text-red-700 transition duration-150 ease-in-out">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm6 0a1 1 0 012 0v6a1 1 0 11-2 0V8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="flex justify-between items-center">
                    <button type="button" wire:click="addTransaction"
                        class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 transition duration-150 ease-in-out">
                        + Add another transaction
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-indigo-600 text-white font-semibold rounded-md hover:bg-indigo-700 transition duration-150 ease-in-out shadow-md">
                        Save Journal Entry
                    </button>
                </div>
            </form>
        </div>

        <!-- Journal Entries List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-gray-100">All Journal Entries</h2>
            <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Description</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                Status</th>
                            <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($entries as $entry)
                            <tr>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $entry['date'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $entry['description'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($entry['is_posted'])
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">Posted</span>
                                    @else
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">Draft</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <a href="#"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">View</a>
                                    <a href="#"
                                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">Delete</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4"
                                    class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">No journal
                                    entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
