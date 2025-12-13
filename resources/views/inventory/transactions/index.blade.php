<x-app-layout>
    {{--
- Filter by: Type, Status, Date Range, Store
- Columns: Reference, Type, Store, Items, Status, Date, Actions
- Status badges (Draft, Finalized, Cancelled)
 --}}

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-primary leading-tight">
                📋 {{ __('Transactions') }} <span class="text-muted text-lg">({{ $transactions->total() }}
                    transactions)</span>
            </h2>
            <x-button.primary href="{{ route('inventory.transactions.create') }}">
                <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                New Transaction
            </x-button.primary>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Search and Filters -->
            <div class="mb-6">
                <form method="GET" action="{{ route('inventory.transactions.index') }}">
                    <div class="surface p-4 rounded-lg border border-secondary">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div>
                                <x-form.label for="search" value="Search" />
                                <x-form.input id="search" name="search" type="text" class="mt-1 block w-full"
                                    :value="request('search')" placeholder="Reference, notes..." />
                            </div>

                            <!-- Type Filter -->
                            <div>
                                <x-form.label for="type" value="Type" />
                                <x-form.select id="type" name="type" class="mt-1 block w-full">
                                    <option value="">All Types</option>
                                    <option value="receipt" {{ request('type') == 'receipt' ? 'selected' : '' }}>📥
                                        Receipt</option>
                                    <option value="issue" {{ request('type') == 'issue' ? 'selected' : '' }}>📤 Issue
                                    </option>
                                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>🔄
                                        Transfer</option>
                                    <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>
                                        📊 Adjustment</option>
                                </x-form.select>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <x-form.label for="status" value="Status" />
                                <x-form.select id="status" name="status" class="mt-1 block w-full">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="finalized" {{ request('status') == 'finalized' ? 'selected' : '' }}>
                                        Finalized</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                </x-form.select>
                            </div>

                            <!-- Store Filter -->
                            <div>
                                <x-form.label for="store_id" value="Store" />
                                <x-form.select id="store_id" name="store_id" class="mt-1 block w-full">
                                    <option value="">All Stores</option>
                                    @foreach ($stores as $store)
                                        <option value="{{ $store->id }}"
                                            {{ request('store_id') == $store->id ? 'selected' : '' }}>
                                            {{ $store->name }}
                                        </option>
                                    @endforeach
                                </x-form.select>
                            </div>

                            <!-- Date Range -->
                            <div class="md:col-span-2">
                                <x-form.label for="date_range" value="Date Range" />
                                <div class="grid grid-cols-2 gap-2">
                                    <x-form.input id="start_date" name="start_date" type="date"
                                        class="mt-1 block w-full" :value="request('start_date')" />
                                    <x-form.input id="end_date" name="end_date" type="date"
                                        class="mt-1 block w-full" :value="request('end_date')" />
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-2 mt-4">
                            <x-button.primary type="submit">
                                Apply Filters
                            </x-button.primary>
                            <x-button.secondary href="{{ route('inventory.transactions.index') }}">
                                Clear
                            </x-button.secondary>
                        </div>
                    </div>
                </form>
            </div>

<!-- Transactions Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if ($transactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reference
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Store
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Items
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total Qty
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($transactions as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900 font-mono">
                                                    <a href="{{ route('inventory.transactions.show', $transaction) }}"
                                                        class="hover:text-blue-600">
                                                        {{ $transaction->reference }}
                                                    </a>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    by {{ $transaction->createdBy->name ?? 'System' }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $typeIcons = [
                                                        'receipt' => '📥',
                                                        'issue' => '📤',
                                                        'transfer' => '🔄',
                                                        'adjustment' => '📊',
                                                    ];
                                                    $typeLabels = [
                                                        'receipt' => 'Receipt',
                                                        'issue' => 'Issue',
                                                        'transfer' => 'Transfer',
                                                        'adjustment' => 'Adjustment',
                                                    ];
                                                @endphp
                                                <div class="flex items-center">
                                                    <span class="text-lg mr-2">{{ $typeIcons[$transaction->type] ?? '📄' }}</span>
                                                    <span class="text-sm text-gray-900">{{ $typeLabels[$transaction->type] ?? ucfirst($transaction->type) }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->store->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->items_count ?? $transaction->items->count() }} items
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $transaction->total_quantity ?? $transaction->items->sum('quantity') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $transaction->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                    {{ $transaction->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $transaction->created_at->format('M j, Y') }}
                                                <div class="text-xs text-gray-400">
                                                    {{ $transaction->created_at->format('g:i A') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('inventory.transactions.show', $transaction) }}"
                                                        class="text-indigo-600 hover:text-indigo-900 text-sm">
                                                        View
                                                    </a>
                                                    @if($transaction->status === 'draft')
                                                        <a href="{{ route('inventory.transactions.edit', $transaction) }}"
                                                            class="text-blue-600 hover:text-blue-900 text-sm">
                                                            Edit
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($transactions->hasPages())
                            <div class="mt-4">
                                {{ $transactions->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <x-heroicon-s-document-text class="mx-auto h-12 w-12 text-muted" />
                            <h3 class="mt-2 text-sm font-medium text-primary">No transactions found</h3>
                            <p class="mt-1 text-sm text-muted">Get started by creating your first transaction.</p>
                            <div class="mt-6">
                                <x-button.primary href="{{ route('inventory.transactions.create') }}">
                                    <x-heroicon-s-plus class="w-4 h-4 mr-2" />
                                    New Transaction
                                </x-button.primary>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
