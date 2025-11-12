@php
    $typeIcons = [
        'receipt' => '📥',
        'issue' => '📤',
        'transfer' => '🔄',
        'adjustment' => '📊'
    ];
    $typeTitles = [
        'receipt' => 'Stock Receipt',
        'issue' => 'Item Issue', 
        'transfer' => 'Stock Transfer',
        'adjustment' => 'Stock Adjustment'
    ];
    $typeColors = [
        'receipt' => 'blue',
        'issue' => 'green',
        'transfer' => 'purple', 
        'adjustment' => 'orange'
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $typeIcons[$transaction->type] }} {{ $typeTitles[$transaction->type] }}
                <span class="text-gray-500 font-mono text-lg">({{ $transaction->reference }})</span>
            </h2>
            <div class="flex space-x-2">
                @if($transaction->isDraft())
                    <x-button.secondary href="{{ route('inventory.transactions.edit', $transaction) }}">
                        <x-heroicon-s-pencil class="w-4 h-4 mr-2" />
                        Edit
                    </x-button.secondary>
                    <x-button.primary onclick="document.getElementById('finalize-form').submit()">
                        <x-heroicon-s-check class="w-4 h-4 mr-2" />
                        Finalize
                    </x-button.primary>
                    <form id="finalize-form" action="{{ route('inventory.transactions.finalize', $transaction) }}" method="POST" class="hidden">
                        @csrf
                        @method('PUT')
                    </form>
                @endif
                
                @if($transaction->isFinalized())
                    <x-button.danger onclick="document.getElementById('cancel-form').submit()">
                        <x-heroicon-s-x-mark class="w-4 h-4 mr-2" />
                        Cancel
                    </x-button.danger>
                    <form id="cancel-form" action="{{ route('inventory.transactions.cancel', $transaction) }}" method="POST" class="hidden">
                        @csrf
                        @method('PUT')
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <!-- Transaction Header -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Transaction Info -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Reference</label>
                                        <p class="mt-1 text-lg font-mono text-gray-900">{{ $transaction->reference }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Status</label>
                                        <div class="mt-1">
                                            <x-status-badge :status="$transaction->status" />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Transaction Date</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ $transaction->transaction_date->format('F j, Y g:i A') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    @if($transaction->type === 'transfer')
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">From Store</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->fromStore->name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">To Store</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->toStore->name }}</p>
                                        </div>
                                    @else
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Store</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->store->name }}</p>
                                        </div>
                                    @endif
                                    
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Created By</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $transaction->createdBy->name ?? 'System' }}</p>
                                    </div>
                                    
                                    @if($transaction->isFinalized() && $transaction->approvedBy)
                                        <div>
                                            <label class="text-sm font-medium text-gray-500">Approved By</label>
                                            <p class="mt-1 text-sm text-gray-900">{{ $transaction->approvedBy->name }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Additional Fields -->
                            @if($transaction->supplier || $transaction->recipient || $transaction->adjustment_reason)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-3">Additional Information</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @if($transaction->supplier)
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Supplier</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $transaction->supplier }}</p>
                                            </div>
                                        @endif
                                        @if($transaction->recipient)
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Recipient</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ $transaction->recipient }}</p>
                                            </div>
                                        @endif
                                        @if($transaction->adjustment_reason)
                                            <div>
                                                <label class="text-xs font-medium text-gray-500">Adjustment Reason</label>
                                                <p class="mt-1 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $transaction->adjustment_reason)) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            
                            @if($transaction->notes)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <label class="text-sm font-medium text-gray-500">Notes</label>
                                    <p class="mt-2 text-sm text-gray-700">{{ $transaction->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions & Stats -->
                <div class="space-y-6">
                    <!-- Transaction Summary -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Transaction Summary</h3>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Items</span>
                                    <span class="font-medium">{{ $transaction->items->count() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Quantity</span>
                                    <span class="font-medium">{{ $transaction->items->sum('quantity') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Value</span>
                                    <span class="font-medium">${{ number_format($transaction->items->sum(function($item) { return $item->quantity * ($item->unit_price / 100); }), 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Created</span>
                                    <span class="font-medium">{{ $transaction->created_at->format('M j, Y') }}</span>
                                </div>
                                @if($transaction->finalized_at)
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Finalized</span>
                                        <span class="font-medium">{{ $transaction->finalized_at->format('M j, Y g:i A') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-semibold mb-4">Actions</h3>
                            <div class="space-y-2">
                                <x-button.link href="{{ route('inventory.transactions.index') }}" class="w-full justify-center">
                                    ← Back to Transactions
                                </x-button.link>
                                @if($transaction->isDraft())
                                    <x-button.primary onclick="document.getElementById('finalize-form').submit()" class="w-full justify-center">
                                        ✅ Finalize Transaction
                                    </x-button.primary>
                                @endif
                                <x-button.outline href="#" class="w-full justify-center">
                                    📄 Print
                                </x-button.outline>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📦 Transaction Items</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                    @if($transaction->notes)
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transaction->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <x-heroicon-s-cube class="h-6 w-6 text-gray-400" />
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->item->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $item->item->category }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono">
                                            {{ $item->item->sku }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->quantity }} {{ $item->item->unit }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            ${{ number_format($item->unit_price / 100, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ${{ number_format(($item->quantity * $item->unit_price) / 100, 2) }}
                                        </td>
                                        @if($item->notes)
                                            <td class="px-6 py-4 text-sm text-gray-500">
                                                {{ $item->notes }}
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">
                                        Total:
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        {{ $transaction->items->sum('quantity') }} units
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        ${{ number_format($transaction->items->sum(function($item) { return ($item->quantity * $item->unit_price) / 100; }), 2) }}
                                    </td>
                                    @if($transaction->notes)
                                        <td></td>
                                    @endif
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>