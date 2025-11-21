<div>
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
                <div class="px-4 sm:px-0">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">
                        Create Voucher
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        Create a new voucher for your organization.
                    </p>
                </div>
            </div>

            <div class="mt-5 md:col-span-2 md:mt-0">
                <form wire:submit="createVoucher">
                    <div class="shadow sm:rounded-md sm:overflow-hidden">
                        <div class="bg-white dark:bg-gray-800 px-4 py-5 sm:p-6">
                            <div class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-6">
                                <!-- Voucher Type -->
                                <div class="sm:col-span-3">
                                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Voucher Type</label>
                                    <select
                                        wire:model.live="type"
                                        id="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    >
                                        <option value="">Select a type...</option>
                                        @foreach($voucherTypes as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Date -->
                                <div class="sm:col-span-3">
                                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                                    <input
                                        wire:model="date"
                                        type="date"
                                        id="date"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                    @error('date')
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Amount -->
                                <div class="sm:col-span-3">
                                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount</label>
                                    <input
                                        wire:model="amount"
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        id="amount"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                    @error('amount')
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="sm:col-span-6">
                                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
                                    <textarea
                                        wire:model="description"
                                        id="description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    ></textarea>
                                    @error('description')
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="sm:col-span-6">
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                    <textarea
                                        wire:model="notes"
                                        id="notes"
                                        rows="3"
                                        placeholder="Optional notes..."
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    ></textarea>
                                    @error('notes')
                                        <span class="text-sm text-red-600 dark:text-red-400">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                class="inline-flex justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled"
                                wire:target="createVoucher"
                            >
                                <span wire:loading.remove>
                                    Create Voucher
                                </span>
                                <span wire:loading class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12c0 5.514 4.486 10 10 10s10-4.486 10-10S17.514 2 12 20.709a8.008 8.008 0 01-8.709 8z"></path>
                                    </svg>
                                    Creating...
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Listeners -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-message', (event) => {
                // You can implement a toast notification system here
                const { message, type } = event.detail;
                
                // Simple alert for now - replace with your notification system
                alert(`${type.toUpperCase()}: ${message}`);
            });
        });
    </script>
</div>