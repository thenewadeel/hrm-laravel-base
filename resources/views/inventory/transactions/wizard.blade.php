@php
    $typeIcons = [
        'receipt' => '📥',
        'issue' => '📤',
        'transfer' => '🔄',
        'adjustment' => '📊',
    ];
    $typeTitles = [
        'receipt' => 'Receive Stock',
        'issue' => 'Issue Items',
        'transfer' => 'Transfer Items',
        'adjustment' => 'Stock Adjustment',
    ];
    // 1. <!-- Type & Basic Info -->
    //    - Transaction type
    //    - Stores involved
    //    - Reference, date

    // 2. <!-- Add Items -->
    //    - Search and select items
    //    - Enter quantities
    //    - Unit prices (if applicable)

    // 3. <!-- Review & Submit -->
    //    - Summary of items
    //    - Total quantities
    //    - Finalize or Save Draft
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $typeIcons[$type] ?? '📋' }} {{ $typeTitles[$type] ?? 'New Transaction' }} • Step 1 of 3
            </h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center">1</div>
                    <span>Basic Info</span>
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center">2</div>
                    <span>Add Items</span>
                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center">3</div>
                    <span>Review</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('inventory.transactions.store') }}" method="POST" id="transaction-form">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="step" value="1">

                        <div class="space-y-6">
                            <!-- Transaction Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">📋 Transaction Details</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div>
                                        <x-form.label for="reference" value="Reference Number *" />
                                        <x-form.input id="reference" name="reference" type="text"
                                            class="mt-1 block w-full" :value="old('reference', $reference ?? '')" required
                                            placeholder="e.g., REC-2024-001" />
                                        <p class="mt-1 text-sm text-gray-500">Unique identifier for this transaction</p>
                                        <x-form.input-error for="reference" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-form.label for="transaction_date" value="Transaction Date *" />
                                        <x-form.input id="transaction_date" name="transaction_date"
                                            type="datetime-local" class="mt-1 block w-full" :value="old(
                                                'transaction_date',
                                                \Carbon\Carbon::now()->format('Y-m-d\TH:i'),
                                            )"
                                            required />
                                        <x-form.input-error for="transaction_date" class="mt-2" />
                                    </div>

                                    <!-- Store Selection -->
                                    @if ($type === 'transfer')
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <x-form.label for="from_store_id" value="From Store *" />
                                                <x-form.select id="from_store_id" name="from_store_id"
                                                    class="mt-1 block w-full" required>
                                                    <option value="">Select Source Store</option>
                                                    @foreach ($stores as $store)
                                                        <option value="{{ $store->id }}"
                                                            {{ old('from_store_id', request('from_store_id')) == $store->id ? 'selected' : '' }}>
                                                            {{ $store->name }}
                                                        </option>
                                                    @endforeach
                                                </x-form.select>
                                                <x-form.input-error for="from_store_id" class="mt-2" />
                                            </div>
                                            <div>
                                                <x-form.label for="to_store_id" value="To Store *" />
                                                <x-form.select id="to_store_id" name="to_store_id"
                                                    class="mt-1 block w-full" required>
                                                    <option value="">Select Destination Store</option>
                                                    @foreach ($stores as $store)
                                                        <option value="{{ $store->id }}"
                                                            {{ old('to_store_id') == $store->id ? 'selected' : '' }}>
                                                            {{ $store->name }}
                                                        </option>
                                                    @endforeach
                                                </x-form.select>
                                                <x-form.input-error for="to_store_id" class="mt-2" />
                                            </div>
                                        </div>
                                    @else
                                        <div>
                                            <x-form.label for="store_id" value="Store *" />
                                            <x-form.select id="store_id" name="store_id" class="mt-1 block w-full"
                                                required>
                                                <option value="">Select Store</option>
                                                @foreach ($stores as $store)
                                                    <option value="{{ $store->id }}"
                                                        {{ old('store_id', request('store_id')) == $store->id ? 'selected' : '' }}>
                                                        {{ $store->name }}
                                                    </option>
                                                @endforeach
                                            </x-form.select>
                                            <x-form.input-error for="store_id" class="mt-2" />
                                        </div>
                                    @endif

                                    <!-- Additional Fields based on type -->
                                    @if ($type === 'receipt')
                                        <div>
                                            <x-form.label for="supplier" value="Supplier/Vendor" />
                                            <x-form.input id="supplier" name="supplier" type="text"
                                                class="mt-1 block w-full" :value="old('supplier')"
                                                placeholder="e.g., Acme Supplies Inc." />
                                            <x-form.input-error for="supplier" class="mt-2" />
                                        </div>
                                    @endif

                                    @if ($type === 'issue')
                                        <div>
                                            <x-form.label for="recipient" value="Recipient/Department" />
                                            <x-form.input id="recipient" name="recipient" type="text"
                                                class="mt-1 block w-full" :value="old('recipient')"
                                                placeholder="e.g., Production Department, John Doe" />
                                            <x-form.input-error for="recipient" class="mt-2" />
                                        </div>
                                    @endif

                                    @if ($type === 'adjustment')
                                        <div>
                                            <x-form.label for="adjustment_reason" value="Adjustment Reason *" />
                                            <x-form.select id="adjustment_reason" name="adjustment_reason"
                                                class="mt-1 block w-full" required>
                                                <option value="">Select Reason</option>
                                                <option value="stock_count"
                                                    {{ old('adjustment_reason') == 'stock_count' ? 'selected' : '' }}>
                                                    Stock Count</option>
                                                <option value="damaged"
                                                    {{ old('adjustment_reason') == 'damaged' ? 'selected' : '' }}>
                                                    Damaged Goods</option>
                                                <option value="expired"
                                                    {{ old('adjustment_reason') == 'expired' ? 'selected' : '' }}>
                                                    Expired Items</option>
                                                <option value="theft"
                                                    {{ old('adjustment_reason') == 'theft' ? 'selected' : '' }}>
                                                    Theft/Loss</option>
                                                <option value="found"
                                                    {{ old('adjustment_reason') == 'found' ? 'selected' : '' }}>Found
                                                    Items</option>
                                                <option value="other"
                                                    {{ old('adjustment_reason') == 'other' ? 'selected' : '' }}>Other
                                                </option>
                                            </x-form.select>
                                            <x-form.input-error for="adjustment_reason" class="mt-2" />
                                        </div>
                                    @endif

                                    <div>
                                        <x-form.label for="notes" value="Notes" />
                                        <x-form.textarea id="notes" name="notes" class="mt-1 block w-full"
                                            rows="3"
                                            placeholder="Add any additional notes about this transaction...">{{ old('notes') }}</x-form.textarea>
                                        <x-form.input-error for="notes" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-between space-x-3 mt-8 pt-8 border-t border-gray-200">
                            <x-button.secondary href="{{ route('inventory.transactions.create') }}">
                                ← Back to Types
                            </x-button.secondary>
                            <x-button.primary type="submit">
                                Next: Add Items →
                            </x-button.primary>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400" />
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">About
                            {{ $typeTitles[$type] ?? 'this transaction' }}</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            @if ($type === 'receipt')
                                <p>Use this to record items coming into your inventory from suppliers, production, or
                                    returns.</p>
                            @elseif($type === 'issue')
                                <p>Use this to record items leaving your inventory for sales, internal usage, or
                                    transfers.</p>
                            @elseif($type === 'transfer')
                                <p>Use this to move items between different store locations while maintaining total
                                    inventory.</p>
                            @elseif($type === 'adjustment')
                                <p>Use this to correct inventory levels after stock counts or to account for
                                    discrepancies.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
