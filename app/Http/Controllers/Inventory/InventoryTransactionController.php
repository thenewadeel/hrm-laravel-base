<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryTransactionController extends Controller
{
    /**
     * Display a listing of inventory transactions.
     */
    public function index(Request $request): View
    {
        $query = Transaction::with(['store', 'createdBy', 'approvedBy']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', '%'.$request->search.'%')
                    ->orWhere('type', 'like', '%'.$request->search.'%')
                    ->orWhere('notes', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->latest()->paginate(20);
        $stores = Store::where('is_active', true)->get();

        return view('inventory.transactions.index', compact('transactions', 'stores'));
    }

    /**
     * Show the form for creating a new inventory transaction.
     */
    public function create(): View
    {
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        return view('inventory.transactions.create-new', compact('stores', 'items'));
    }

    /**
     * Show the transaction wizard.
     */
    public function wizard(Request $request): View
    {
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        // Get transaction type from request or default to 'receipt'
        $type = $request->get('type', 'receipt');

        // Define transaction type icons and titles to match the view
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

        return view('inventory.transactions.wizard', compact('stores', 'items', 'type', 'typeIcons', 'typeTitles'));
    }

    /**
     * Handle wizard step 1 submission.
     */
    public function wizardStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:receipt,issue,transfer,adjustment',
            'reference' => 'required|string|unique:inventory_transactions,reference',
            'transaction_date' => 'required|date',
            'store_id' => 'required_if:type,receipt,issue,adjustment|exists:inventory_stores,id',
            'from_store_id' => 'required_if:type,transfer|exists:inventory_stores,id',
            'to_store_id' => 'required_if:type,transfer|exists:inventory_stores,id|different:from_store_id',
            'supplier' => 'nullable|string',
            'recipient' => 'nullable|string',
            'adjustment_reason' => 'required_if:type,adjustment|string|in:stock_count,damaged,expired,theft,found,other',
            'notes' => 'nullable|string',
        ]);

        // Store wizard data in session
        session(['transaction_wizard' => $validated]);

        return redirect()->route('inventory.transactions.wizard.step2');
    }

    /**
     * Show wizard step 2 - Add items.
     */
    public function wizardStep2(Request $request): View
    {
        // Check if wizard data exists
        if (! session()->has('transaction_wizard')) {
            return redirect()->route('inventory.transactions.wizard')
                ->with('error', 'Please complete step 1 first');
        }

        $wizardData = session('transaction_wizard');
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        // Get transaction type and related data
        $type = $wizardData['type'];
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

        return view('inventory.transactions.wizard-step2', compact(
            'wizardData', 'stores', 'items', 'type', 'typeIcons', 'typeTitles'
        ));
    }

    /**
     * Handle wizard step 2 submission (items) and create transaction.
     */
    public function wizardStep2Submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Get wizard data from session
        $wizardData = session('transaction_wizard');

        if (! $wizardData) {
            return redirect()->route('inventory.transactions.wizard')
                ->with('error', 'Wizard session expired. Please start over.');
        }

        // Merge wizard data with items
        $transactionData = array_merge($wizardData, $validated);

        // Determine the store_id based on transaction type
        if ($transactionData['type'] === 'transfer') {
            $transactionData['store_id'] = $transactionData['from_store_id'];
        }

        // Create transaction with items
        $transaction = Transaction::create([
            'store_id' => $transactionData['store_id'],
            'type' => $transactionData['type'],
            'reference' => $transactionData['reference'],
            'transaction_date' => $transactionData['transaction_date'],
            'notes' => $transactionData['notes'] ?? null,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        // Add transaction items
        foreach ($transactionData['items'] as $itemData) {
            $transaction->items()->create([
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'] ?? null,
                'notes' => $itemData['notes'] ?? null,
            ]);
        }

        // Clear wizard session
        session()->forget('transaction_wizard');

        return redirect()->route('inventory.transactions.show', $transaction)
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Store a newly created inventory transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:receipt,issue,transfer,adjustment',
            'reference' => 'required|string|unique:inventory_transactions,reference',
            'transaction_date' => 'required|date',
            'store_id' => 'required_if:type,receipt,issue,adjustment|exists:inventory_stores,id',
            'from_store_id' => 'required_if:type,transfer|exists:inventory_stores,id',
            'to_store_id' => 'required_if:type,transfer|exists:inventory_stores,id|different:from_store_id',
            'supplier' => 'nullable|string',
            'recipient' => 'nullable|string',
            'adjustment_reason' => 'required_if:type,adjustment|string|in:stock_count,damaged,expired,theft,found,other',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Determine the store_id based on transaction type
        $storeId = match ($validated['type']) {
            'transfer' => $validated['from_store_id'],
            default => $validated['store_id'],
        };

        // Create transaction with items
        $transaction = Transaction::create([
            'store_id' => $storeId,
            'to_store_id' => $validated['to_store_id'] ?? null,
            'type' => $validated['type'],
            'reference' => $validated['reference'],
            'transaction_date' => $validated['transaction_date'],
            'supplier' => $validated['supplier'] ?? null,
            'recipient' => $validated['recipient'] ?? null,
            'adjustment_reason' => $validated['adjustment_reason'] ?? null,
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        // Add transaction items
        foreach ($validated['items'] as $itemData) {
            $transaction->items()->create([
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'] ?? null,
                'notes' => $itemData['notes'] ?? null,
            ]);
        }

        return redirect()->route('inventory.transactions.show', $transaction)
            ->with('success', 'Transaction created successfully.');
    }

    /**
     * Display the specified inventory transaction.
     */
    public function show(Transaction $transaction): View
    {
        $transaction->load(['store', 'createdBy', 'approvedBy', 'items.item']);

        return view('inventory.transactions.show', compact('transaction'));
    }

    /**
     * Finalize a transaction.
     */
    public function finalize(Transaction $transaction): RedirectResponse
    {
        if ($transaction->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft transactions can be finalized.');
        }

        $transaction->update([
            'status' => 'completed',
            'finalized_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Update inventory quantities based on transaction type
        foreach ($transaction->items as $transactionItem) {
            switch ($transaction->type) {
                case 'receipt':
                    // Increase quantity in source store
                    $this->updateStoreItemQuantity($transaction->store_id, $transactionItem->item_id, $transactionItem->quantity, 'add');
                    break;

                case 'issue':
                    // Decrease quantity in source store
                    $this->updateStoreItemQuantity($transaction->store_id, $transactionItem->item_id, $transactionItem->quantity, 'subtract');
                    break;

                case 'transfer':
                    // Decrease from source store, increase in destination store
                    $this->updateStoreItemQuantity($transaction->store_id, $transactionItem->item_id, $transactionItem->quantity, 'subtract');
                    if ($transaction->to_store_id) {
                        $this->updateStoreItemQuantity($transaction->to_store_id, $transactionItem->item_id, $transactionItem->quantity, 'add');
                    }
                    break;

                case 'adjustment':
                    // Adjustments can be positive or negative based on context
                    $this->updateStoreItemQuantity($transaction->store_id, $transactionItem->item_id, $transactionItem->quantity, 'add');
                    break;
            }
        }

        return redirect()->back()->with('success', 'Transaction finalized successfully.');
    }

    /**
     * Show the form for editing the specified inventory transaction.
     */
    public function edit(Transaction $transaction): View
    {
        // Only allow editing of draft transactions
        if ($transaction->status !== 'draft') {
            return redirect()->route('inventory.transactions.show', $transaction)
                ->with('error', 'Only draft transactions can be edited.');
        }

        $transaction->load(['store', 'createdBy', 'approvedBy', 'items.item']);
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        return view('inventory.transactions.edit-new', compact('transaction', 'stores', 'items'));
    }

    /**
     * Update the specified inventory transaction.
     */
    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        // Only allow updating of draft transactions
        if ($transaction->status !== 'draft') {
            return redirect()->route('inventory.transactions.show', $transaction)
                ->with('error', 'Only draft transactions can be updated.');
        }

        $validated = $request->validate([
            'store_id' => 'required|exists:inventory_stores,id',
            'reference' => 'required|string|unique:inventory_transactions,reference,'.$transaction->id,
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Update transaction
        $transaction->update([
            'store_id' => $validated['store_id'],
            'reference' => $validated['reference'],
            'transaction_date' => $validated['transaction_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Remove existing items and add new ones
        $transaction->items()->delete();

        foreach ($validated['items'] as $itemData) {
            $transaction->items()->create([
                'item_id' => $itemData['item_id'],
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['unit_price'] ?? null,
                'notes' => $itemData['notes'] ?? null,
            ]);
        }

        return redirect()->route('inventory.transactions.show', $transaction)
            ->with('success', 'Transaction updated successfully.');
    }

    /**
     * Remove the specified inventory transaction.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        // Only allow deletion of draft transactions
        if ($transaction->status !== 'draft') {
            return redirect()->route('inventory.transactions.show', $transaction)
                ->with('error', 'Only draft transactions can be deleted.');
        }

        $transaction->items()->delete();
        $transaction->delete();

        return redirect()->route('inventory.transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    /**
     * Cancel a transaction.
     */
    public function cancel(Transaction $transaction): RedirectResponse
    {
        if ($transaction->status === 'completed') {
            return redirect()->back()->with('error', 'Completed transactions cannot be cancelled.');
        }

        $transaction->update(['status' => 'cancelled']);

        return redirect()->back()->with('success', 'Transaction cancelled successfully.');
    }

    /**
     * Helper method to update store item quantity.
     */
    private function updateStoreItemQuantity(int $storeId, int $itemId, int $quantity, string $operation): void
    {
        $store = \App\Models\Inventory\Store::find($storeId);
        if (! $store) {
            return;
        }

        // Check if item exists in the store's inventory (pivot table)
        $existingPivot = $store->items()->where('item_id', $itemId)->first();

        if ($existingPivot) {
            // Update existing pivot record
            $currentQuantity = $existingPivot->pivot->quantity ?? 0;
            $newQuantity = match ($operation) {
                'add' => $currentQuantity + $quantity,
                'subtract' => max(0, $currentQuantity - $quantity), // Prevent negative quantities
                default => $currentQuantity,
            };

            // Update the pivot table
            $store->items()->updateExistingPivot($itemId, [
                'quantity' => $newQuantity,
                'updated_at' => now(),
            ]);
        } else {
            // Create new pivot record only for add operations
            if ($operation === 'add') {
                $store->items()->attach($itemId, [
                    'quantity' => $quantity,
                    'min_stock' => 0, // Default min stock
                    'max_stock' => 999999, // Default max stock
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
