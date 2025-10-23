<?php

namespace App\Services;

use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use App\Models\Inventory\TransactionItem;
use App\Models\Organization;
use App\Models\User;
use App\Permissions\InventoryPermissions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class InventoryService
{
    /**
     * Create a new store
     */
    public function createStore(array $data, User $user): Store
    {
        // dd($user->getAllPermissions());
        // dd(Gate::authorize('create', Store::class));
        Gate::authorize('create', Store::class);


        return DB::transaction(function () use ($data, $user) {
            $store = Store::create($data);

            // Log activity or trigger events here if needed
            // activity()->log("Store {$store->name} created by {$user->name}");

            return $store->load('organization_unit');
        });
    }

    /**
     * Update store inventory - add/update items with min/max stock
     */
    public function updateStoreInventory(
        Store $store,
        Item $item,
        int $quantity,
        User $user,
        ?int $minStock = null,
        ?int $maxStock = null
    ): void {
        // dd($user->getAllPermissions());
        Gate::authorize('manageInventory', $store);

        // Check if item belongs to same organization
        if ($item->organization_id !== $store->organization->id) {
            throw new \Exception('Item does not belong to the same organization');
        }

        DB::transaction(function () use ($store, $item, $quantity, $minStock, $maxStock) {
            $pivotData = ['quantity' => max(0, $quantity)];

            if (!is_null($minStock)) {
                $pivotData['min_stock'] = $minStock;
            }

            if (!is_null($maxStock)) {
                $pivotData['max_stock'] = $maxStock;
            }

            $store->items()->syncWithoutDetaching([$item->id => $pivotData]);

            // Update store timestamp
            $store->touch();
        });
    }



    /**
     * Adjust store inventory (increment/decrement)
     */
    public function adjustStoreInventory(Store $store, Item $item, int $adjustment, User $user): void
    {
        // Gate::authorize('manageInventory', $store);

        DB::transaction(function () use ($store, $item, $adjustment) {
            $currentQuantity = $store->getItemQuantity($item);
            $newQuantity = max(0, $currentQuantity + $adjustment);

            $store->items()->syncWithoutDetaching([
                $item->id => ['quantity' => $newQuantity]
            ]);

            // Log adjustment activity
            // activity()->log("Inventory adjusted for {$item->name} in {$store->name}: {$adjustment}");
        });
    }

    /**
     * Create a draft transaction
     */
    public function createTransaction(array $data, User $user): Transaction
    {
        // Gate::authorize('create', Transaction::class);

        return DB::transaction(function () use ($data, $user) {
            $transaction = Transaction::create([
                ...$data,
                'created_by' => $user->id,
                'status' => Transaction::STATUS_DRAFT,
            ]);

            return $transaction->load(['store', 'createdBy']);
        });
    }

    /**
     * Add items to a draft transaction
     */
    public function addItemsToTransaction(Transaction $transaction, array $items, User $user): Transaction
    {
        // Gate::authorize('update', $transaction);

        if (!$transaction->isDraft()) {
            throw new \Exception('Cannot modify finalized or cancelled transaction');
        }

        return DB::transaction(function () use ($transaction, $items) {
            foreach ($items as $itemData) {
                $transaction->items()->create([
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => (int)($itemData['unit_price'] * 100), // Convert to cents
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            return $transaction->load('items.item');
        });
    }

    /**
     * Finalize a transaction and update store inventory
     */
    public function finalizeTransaction(Transaction $transaction, User $user): Transaction
    {
        // Gate::authorize('finalize', $transaction);

        if (!$transaction->isDraft()) {
            throw new \Exception('Transaction is not in draft status');
        }

        if ($transaction->items->isEmpty()) {
            throw new \Exception('Cannot finalize transaction with no items');
        }

        return DB::transaction(function () use ($transaction, $user) {
            // Update store inventory based on transaction type
            foreach ($transaction->items as $transactionItem) {
                $adjustment = $this->getQuantityAdjustment($transaction->type, $transactionItem->quantity);
                $this->adjustStoreInventory($transaction->store, $transactionItem->item, $adjustment, $user);
            }

            // Mark transaction as finalized
            $transaction->update([
                'status' => Transaction::STATUS_FINALIZED,
                'finalized_at' => now(),
                'approved_by' => $user->id,
            ]);

            // Trigger events or notifications
            // event(new TransactionFinalized($transaction));

            return $transaction->load(['store', 'items.item', 'createdBy', 'approvedBy']);
        });
    }

    /**
     * Cancel a transaction
     */
    public function cancelTransaction(Transaction $transaction, User $user): Transaction
    {
        // Gate::authorize('cancel', $transaction);

        if ($transaction->isFinalized()) {
            throw new \Exception('Cannot cancel finalized transaction');
        }

        return DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => Transaction::STATUS_CANCELLED,
            ]);

            return $transaction;
        });
    }

    /**
     * Get store stock levels with detailed information
     */
    public function getStoreStockLevels(Store $store, User $user): array
    {
        Gate::authorize('view', $store);

        $items = $store->items()
            ->withPivot('quantity', 'min_stock', 'max_stock')
            ->get()
            ->map(function ($item) {
                $quantity = $item->pivot->quantity;
                $minStock = $item->pivot->min_stock;
                $maxStock = $item->pivot->max_stock;
                $reorderLevel = $item->reorder_level;
                // dd([
                //     'quantity' => $quantity,
                //     'min_stock' => $minStock,
                //     'max_stock' => $maxStock,
                //     'reorder_level' => $reorderLevel,
                //     'is_low_stock' => $quantity <= ($minStock ?? $reorderLevel),
                //     'is_out_of_stock' => $quantity <= 0,
                //     'is_overstock' => $maxStock && $quantity > $maxStock
                // ]);

                // Determine stock status
                $isLowStock = $quantity <= ($minStock ?? $reorderLevel);
                $isOutOfStock = $quantity <= 0;
                $isOverstock = $maxStock && $quantity > $maxStock;

                return [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'sku' => $item->sku,
                    'category' => $item->category,
                    'current_stock' => $quantity,
                    'min_stock' => $minStock,
                    'max_stock' => $maxStock,
                    'reorder_level' => $reorderLevel,
                    'is_low_stock' => $isLowStock,
                    'is_out_of_stock' => $isOutOfStock,
                    'is_overstock' => $isOverstock,
                    'status' => $isOutOfStock ? 'out_of_stock' : ($isLowStock ? 'low_stock' : ($isOverstock ? 'overstock' : 'normal')),
                ];
            });

        $summary = [
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('current_stock'),
            'low_stock_items' => $items->where('is_low_stock', true)->count(),
            'out_of_stock_items' => $items->where('is_out_of_stock', true)->count(),
            'overstock_items' => $items->where('is_overstock', true)->count(),
            'normal_stock_items' => $items->where('status', 'normal')->count(),
        ];

        return [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'code' => $store->code,
                'location' => $store->location,
            ],
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * Get item availability across all stores in organization
     */
    public function getItemAvailability(Item $item, User $user): array
    {
        Gate::authorize('view', $item);

        $stores = $item->stores()
            ->get()
            ->map(function ($store) use ($item) {
                $quantity = $store->pivot->quantity ?? 0;
                $reorderLevel = $item->reorder_level ?? 0;

                return [
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'store_code' => $store->code,
                    'location' => $store->location,
                    'quantity' => $quantity,
                    'min_stock' => $store->pivot->min_stock ?? null,
                    'max_stock' => $store->pivot->max_stock ?? null,
                    'is_low_stock' => $quantity <= $reorderLevel,
                    'is_out_of_stock' => $quantity <= 0,
                ];
            });

        $totalQuantity = $stores->sum('quantity');
        $reorderLevel = $item->reorder_level ?? 0;

        $totalQuantity = $stores->sum('quantity');
        $storesWithStock = $stores->where('quantity', '>', 0);
        $lowStockStores = $stores->where('is_low_stock', true);

        // dd($stores);

        return [
            'item' => [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'description' => $item->description,
                'category' => $item->category,
                'reorder_level' => $reorderLevel,
                'total_quantity' => $totalQuantity,
                'is_low_stock_overall' => $totalQuantity <= $reorderLevel,
                'is_out_of_stock_overall' => $totalQuantity <= 0,
            ],
            'availability' => $stores,
            'summary' => [
                'total_quantity' => $totalQuantity,
                'stores_count' => $stores->count(),
                'stores_with_stock' => $storesWithStock->count(),
                'low_stock_stores' => $lowStockStores->count(),
                'out_of_stock_stores' => $stores->where('quantity', '<=', 0)->count(),
            ],
            'stores_with_stock' => $storesWithStock->values(),
            'low_stock_locations' => $lowStockStores->values(),
        ];
    }

    /**
     * Helper method to determine quantity adjustment based on transaction type
     */
    private function getQuantityAdjustment(string $transactionType, int $quantity): int
    {
        return match ($transactionType) {
            Transaction::TYPE_INCOMING => $quantity, // Increase stock
            Transaction::TYPE_OUTGOING => -$quantity, // Decrease stock
            Transaction::TYPE_ADJUSTMENT => $quantity, // Can be positive or negative
            default => 0,
        };
    }
}
