<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryStockController extends Controller
{
    /**
     * Display stock management dashboard.
     */
    public function index(): View
    {
        // Get recent stock adjustments
        $recentAdjustments = Transaction::where('type', 'adjustment')
            ->with(['store', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent stock transfers
        $recentTransfers = Transaction::where('type', 'transfer')
            ->with(['store', 'toStore', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        // Get recent stock counts
        $recentCounts = Transaction::where('type', 'count')
            ->with(['store', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        // Calculate total stock value across all stores
        $stores = Store::with('items')->get();
        $totalStockValue = $stores->sum(function ($store) {
            return $store->items->sum(function ($item) {
                return $item->pivot->quantity * ($item->cost_price ?? 0);
            });
        });

        // Count pending adjustments (draft status)
        $pendingAdjustments = Transaction::where('type', 'adjustment')
            ->where('status', 'draft')
            ->count();

        // Count active transfers (non-completed transfers)
        $activeTransfers = Transaction::where('type', 'transfer')
            ->whereIn('status', ['draft', 'pending'])
            ->count();

        // Count stock counts this month
        $monthlyCounts = Transaction::where('type', 'count')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Get critical stock items (below reorder level)
        $criticalStockItems = 0;
        foreach ($stores as $store) {
            $criticalStockItems += $store->lowStockItems()->count();
        }

        // Get pending approvals (if approval system is implemented)
        $pendingApprovals = Transaction::whereIn('type', ['adjustment', 'transfer'])
            ->where('status', 'pending')
            ->count();

        // Get overdue counts (if count scheduling is implemented)
        $overdueCounts = 0; // Placeholder - would depend on count scheduling system

        // Get scheduled transfers (if transfer scheduling is implemented)
        $scheduledTransfers = 0; // Placeholder - would depend on transfer scheduling system

        // Get store stock summary
        $storeStockSummary = $stores->map(function ($store) {
            return (object) [
                'name' => $store->name,
                'total_items' => $store->items->count(),
                'total_quantity' => $store->items->sum('pivot.quantity'),
                'total_value' => $store->items->sum(function ($item) {
                    return $item->pivot->quantity * ($item->cost_price ?? 0);
                }),
            ];
        });

        return view('inventory.stock.index', compact(
            'recentAdjustments',
            'recentTransfers',
            'recentCounts',
            'totalStockValue',
            'pendingAdjustments',
            'activeTransfers',
            'monthlyCounts',
            'criticalStockItems',
            'pendingApprovals',
            'overdueCounts',
            'scheduledTransfers',
            'storeStockSummary'
        ));
    }

    /**
     * Show stock adjustment form.
     */
    public function adjustment(): View
    {
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        // Get recent stock adjustments for display
        $recentAdjustments = Transaction::where('type', 'adjustment')
            ->with(['store', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        return view('inventory.stock.adjustment', compact('stores', 'items', 'recentAdjustments'));
    }

    /**
     * Process stock adjustment.
     */
    public function processAdjustment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:inventory_stores,id',
            'adjustments' => 'required|array|min:1',
            'adjustments.*.item_id' => 'required|exists:inventory_items,id',
            'adjustments.*.quantity' => 'required|integer',
            'adjustments.*.reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Create adjustment transaction
        $transaction = Transaction::create([
            'store_id' => $validated['store_id'],
            'type' => 'adjustment',
            'reference' => 'ADJ-'.date('Ymd-His'),
            'transaction_date' => now(),
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
            'status' => 'completed',
            'finalized_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Process adjustments
        foreach ($validated['adjustments'] as $adjustment) {
            $item = Item::find($adjustment['item_id']);
            $store = Store::find($validated['store_id']);
            $transaction->items()->create([
                'item_id' => $adjustment['item_id'],
                'quantity' => abs($adjustment['quantity']),
                'unit_price' => $item->unit_price ?? 0, // Add this line
                'notes' => $adjustment['reason'],
            ]);
            // Update store item quantity
            $storeItem = $transaction->store->items()
                ->where('item_id', $adjustment['item_id'])
                ->first();

            if ($storeItem) {
                $newQuantity = $storeItem->pivot->quantity + $adjustment['quantity'];
                $store->items()->updateExistingPivot($adjustment['item_id'], [
                    'quantity' => max(0, $newQuantity),
                ]);
                // dd($transaction->all());
            }
        }

        return redirect()->route('inventory.transactions.show', $transaction)
            ->with('success', 'Stock adjustment completed successfully.');
    }

    /**
     * Show stock count form.
     */
    public function count(): View
    {
        $stores = Store::where('is_active', true)->get();

        // Get recent stock counts for display
        $recentCounts = Transaction::where('type', 'count')
            ->with(['store', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        return view('inventory.stock.count', compact('stores', 'recentCounts'));
    }

    /**
     * Process stock count.
     */
    public function processCount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:inventory_stores,id',
            'counts' => 'required|array|min:1',
            'counts.*.item_id' => 'required|exists:inventory_items,id',
            'counts.*.counted_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Create count transaction
        $transaction = Transaction::create([
            'store_id' => $validated['store_id'],
            'type' => 'count',
            'reference' => 'CNT-'.date('Ymd-His'),
            'transaction_date' => now(),
            'notes' => $validated['notes'],
            'created_by' => auth()->id(),
            'status' => 'completed',
            'finalized_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Process counts and create adjustments
        foreach ($validated['counts'] as $count) {
            $storeItem = $transaction->store->items()
                ->where('item_id', $count['item_id'])
                ->first();

            if ($storeItem) {
                $difference = $count['counted_quantity'] - $storeItem->pivot->quantity;
                if ($difference !== 0) {
                    $item = Item::find($count['item_id']);
                    $store = Store::find($validated['store_id']);
                    $transaction->items()->create([
                        'item_id' => $count['item_id'],
                        'quantity' => abs($difference),
                        'unit_price' => $item->unit_price ?? 0, // Add this line
                        'notes' => 'Stock count adjustment',
                    ]);

                    // Update to counted quantity
                    $store->items()->updateExistingPivot($count['item_id'], [
                        'quantity' => $count['counted_quantity'],
                    ]);
                }
            }
        }

        return redirect()->route('inventory.transactions.show', $transaction)
            ->with('success', 'Stock count completed successfully.');
    }

    /**
     * Show stock transfer form.
     */
    public function transfer(): View
    {
        $stores = Store::where('is_active', true)->get();
        $items = Item::where('is_active', true)->get();

        // Get recent stock transfers for display
        $recentTransfers = Transaction::where('type', 'transfer')
            ->with(['store', 'toStore', 'createdBy'])
            ->latest()
            ->limit(5)
            ->get();

        return view('inventory.stock.transfer', compact('stores', 'items', 'recentTransfers'));
    }

    /**
     * Process stock transfer.
     */
    public function processTransfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_store_id' => 'required|exists:inventory_stores,id',
            'to_store_id' => 'required|exists:inventory_stores,id|different:from_store_id',
            'transfers' => 'required|array|min:1',
            'transfers.*.item_id' => 'required|exists:inventory_items,id',
            'transfers.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        // Create issue transaction for source store
        $outTransaction = Transaction::create([
            'store_id' => $validated['from_store_id'],
            'type' => 'issue',
            'reference' => 'TRF-OUT-'.date('Ymd-His'),
            'transaction_date' => now(),
            'notes' => $validated['notes'].' (Transfer out)',
            'created_by' => auth()->id(),
            'status' => 'finalized',
            'finalized_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        // Create receipt transaction for destination store
        $inTransaction = Transaction::create([
            'store_id' => $validated['to_store_id'],
            'type' => 'receipt',
            'reference' => 'TRF-IN-'.date('Ymd-His'),
            'transaction_date' => now(),
            'notes' => $validated['notes'].' (Transfer in)',
            'created_by' => auth()->id(),
            'status' => 'finalized',
            'finalized_at' => now(),
            'approved_by' => auth()->id(),
        ]);
        // Process transfers
        foreach ($validated['transfers'] as $transfer) {
            $item = Item::find($transfer['item_id']);

            // Out transaction item
            $outTransaction->items()->create([
                'item_id' => $transfer['item_id'],
                'quantity' => $transfer['quantity'],
                'unit_price' => $item->unit_price ?? 0, // Add this line
                'notes' => 'Transfer to '.Store::find($validated['to_store_id'])->name,
            ]);

            // In transaction item
            $inTransaction->items()->create([
                'item_id' => $transfer['item_id'],
                'quantity' => $transfer['quantity'],
                'unit_price' => $item->unit_price ?? 0, // Add this line
                'notes' => 'Transfer from '.Store::find($validated['from_store_id'])->name,
            ]);

            // Update quantities
            $fromStoreItem = Store::find($validated['from_store_id'])
                ->items()
                ->where('item_id', $transfer['item_id'])
                ->first();

            $toStoreItem = Store::find($validated['to_store_id'])
                ->items()
                ->where('item_id', $transfer['item_id'])
                ->first();

            if ($fromStoreItem) {
                $fromStoreItem->pivot->update([
                    'quantity' => max(0, $fromStoreItem->pivot->quantity - $transfer['quantity']),
                ]);
            }

            if ($toStoreItem) {
                $toStoreItem->pivot->update([
                    'quantity' => $toStoreItem->pivot->quantity + $transfer['quantity'],
                ]);
            }
            // dd([
            //     'transfers' => $validated['transfers'],
            //     'fromStoreItem' => $fromStoreItem->pivot->quantity,
            //     'toStoreItem' => $toStoreItem->pivot->quantity
            // ]);
        }

        return redirect()->route('inventory.transactions.show', $outTransaction)
            ->with('success', 'Stock transfer completed successfully.');
    }

    /**
     * Create a transaction item with required fields
     */
    private function createTransactionItem($transaction, $itemId, $quantity, $notes = '')
    {
        $item = Item::find($itemId);

        return $transaction->items()->create([
            'item_id' => $itemId,
            'quantity' => $quantity,
            'unit_price' => $item->unit_price ?? 0,
            'notes' => $notes,
        ]);
    }
}
