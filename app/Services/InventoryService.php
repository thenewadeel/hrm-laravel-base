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
     * Update store inventory - add/update items
     */
    public function updateStoreInventory(Store $store, Item $item, int $quantity, User $user): void
    {
        // Gate::authorize('manageInventory', $store);

        DB::transaction(function () use ($store, $item, $quantity) {
            $store->items()->syncWithoutDetaching([
                $item->id => ['quantity' => max(0, $quantity)]
            ]);

            // Update store totals or trigger events
            $store->touch(); // Update timestamp
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
     * Get stock levels for a store
     */
    public function getStoreStockLevels(Store $store, User $user): array
    {
        // Gate::authorize('view', $store);

        $items = $store->items()
            ->withPivot('quantity')
            ->get()
            ->map(function ($item) {
                return [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'sku' => $item->sku,
                    'current_stock' => $item->pivot->quantity,
                    'reorder_level' => $item->reorder_level,
                    'is_low_stock' => $item->pivot->quantity <= $item->reorder_level,
                    'is_out_of_stock' => $item->pivot->quantity <= 0,
                ];
            });

        return [
            'store' => $store->only(['id', 'name', 'code']),
            'total_items' => $items->count(),
            'low_stock_items' => $items->where('is_low_stock', true)->count(),
            'out_of_stock_items' => $items->where('is_out_of_stock', true)->count(),
            'items' => $items,
        ];
    }

    /**
     * Get item availability across all stores in organization
     */
    public function getItemAvailability(Item $item, User $user): array
    {
        // Gate::authorize('view', $item);

        $availability = $item->stores()
            ->where('inventory_store_items.quantity', '>', 0)
            ->get()
            ->map(function ($store) {
                return [
                    'store_id' => $store->id,
                    'store_name' => $store->name,
                    'store_code' => $store->code,
                    'quantity' => $store->pivot->quantity,
                    'location' => $store->location,
                ];
            });

        return [
            'item' => $item->only(['id', 'name', 'sku', 'total_quantity']),
            'availability' => $availability,
            'total_available' => $availability->sum('quantity'),
            'stores_count' => $availability->count(),
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
