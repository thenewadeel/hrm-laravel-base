<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

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
                $q->where('reference', 'like', '%' . $request->search . '%')
                    ->orWhere('type', 'like', '%' . $request->search . '%')
                    ->orWhere('notes', 'like', '%' . $request->search . '%');
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

        return view('inventory.transactions.create', compact('stores', 'items'));
    }

    /**
     * Show the transaction wizard.
     */
    public function wizard(): View
    {
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        return view('inventory.transactions.wizard', compact('stores', 'items'));
    }

    /**
     * Store a newly created inventory transaction.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:inventory_stores,id',
            'type' => 'required|string|in:in,out,transfer,adjustment',
            'reference' => 'required|string|unique:inventory_transactions,reference',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        // Create transaction with items
        $transaction = Transaction::create([
            'store_id' => $validated['store_id'],
            'type' => $validated['type'],
            'reference' => $validated['reference'],
            'transaction_date' => $validated['transaction_date'],
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
            $storeItem = $transaction->store->items()
                ->where('item_id', $transactionItem->item_id)
                ->first();

            if ($storeItem) {
                $newQuantity = $transaction->type === 'in'
                    ? $storeItem->quantity + $transactionItem->quantity
                    : $storeItem->quantity - $transactionItem->quantity;

                $storeItem->update(['quantity' => $newQuantity]);
            }
        }

        return redirect()->back()->with('success', 'Transaction finalized successfully.');
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
}
