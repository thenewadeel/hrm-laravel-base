<div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Create Cash Receipt</h2>
        
        <form wire:submit="createReceipt">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-form.label for="date" value="Date" />
                    <x-form.input
                        wire:model="date"
                        id="date"
                        type="date"
                        class="mt-1 block w-full"
                        required
                    />
                    <x-form.input-error for="date" class="mt-2" />
                </div>

                <div>
                    <x-form.label for="received_from" value="Received From" />
                    <x-form.input
                        wire:model="received_from"
                        id="received_from"
                        class="mt-1 block w-full"
                        required
                    />
                    <x-form.input-error for="received_from" class="mt-2" />
                </div>

                <div>
                    <x-form.label for="amount" value="Amount" />
                    <x-form.input
                        wire:model="amount"
                        id="amount"
                        type="number"
                        step="0.01"
                        min="0.01"
                        class="mt-1 block w-full"
                        required
                    />
                    <x-form.input-error for="amount" class="mt-2" />
                </div>

                <div>
                    <x-form.label for="cash_account_id" value="Cash Account" />
                    <x-form.select
                        wire:model="cash_account_id"
                        id="cash_account_id"
                        class="mt-1 block w-full"
                        required
                    >
                        <option value="">Select Cash Account</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                        @endforeach
                    </x-form.select>
                    <x-form.input-error for="cash_account_id" class="mt-2" />
                </div>

                <div>
                    <x-form.label for="credit_account_id" value="Credit Account" />
                    <x-form.select
                        wire:model="credit_account_id"
                        id="credit_account_id"
                        class="mt-1 block w-full"
                        required
                    >
                        <option value="">Select Credit Account</option>
                        @foreach($creditAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                        @endforeach
                    </x-form.select>
                    <x-form.input-error for="credit_account_id" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-form.label for="description" value="Description" />
                    <x-form.input
                        wire:model="description"
                        id="description"
                        class="mt-1 block w-full"
                    />
                    <x-form.input-error for="description" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                    <x-form.label for="notes" value="Notes" />
                    <x-form.textarea
                        wire:model="notes"
                        id="notes"
                        rows="3"
                        class="mt-1 block w-full"
                    />
                    <x-form.input-error for="notes" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    wire:click="$set('received_from', '')"
                    class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Clear
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Create Receipt
                </button>
            </div>
        </form>
    </div>
</div>
