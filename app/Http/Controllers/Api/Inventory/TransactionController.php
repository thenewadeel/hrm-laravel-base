<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Transaction::class);

        $transactions = Transaction::with(['store', 'items', 'createdBy'])
            ->whereHas('store', function ($query) use ($request) {
                $query->where('organization_id', $request->user()->currentOrganization->id);
            })
            ->get();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Transaction::class);

        $validated = $request->validate([
            'store_id' => 'required|exists:inventory_stores,id',
            'type' => 'required|string|in:incoming,outgoing,adjustment',
            'reference' => 'required|string|unique:inventory_transactions,reference',
            'notes' => 'nullable|string',
            'transaction_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        $transaction = Transaction::create([
            'store_id' => $validated['store_id'],
            'type' => $validated['type'],
            'reference' => $validated['reference'],
            'notes' => $validated['notes'],
            'transaction_date' => $validated['transaction_date'],
            'created_by' => $request->user()->id,
            'status' => 'draft'
        ]);

        foreach ($validated['items'] as $item) {
            $transaction->items()->create([
                'item_id' => $item['item_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'] * 100, // Convert to cents
            ]);
        }

        return response()->json($transaction->load('items'), 201);
    }

    public function show(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);

        $transaction->load(['store', 'items.item', 'createdBy', 'approvedBy']);

        return response()->json($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        Gate::authorize('update', $transaction);

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0'
        ]);

        $transaction->update($validated);

        if (isset($validated['items'])) {
            $transaction->items()->delete();
            foreach ($validated['items'] as $item) {
                $transaction->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] * 100,
                ]);
            }
        }

        return response()->json($transaction->load('items'));
    }

    public function destroy(Transaction $transaction)
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return response()->json(null, 204);
    }

    public function finalize(Transaction $transaction)
    {
        Gate::authorize('finalize', $transaction);

        if (!$transaction->finalize()) {
            return response()->json(['message' => 'Transaction cannot be finalized'], 422);
        }

        return response()->json(['message' => 'Transaction finalized successfully']);
    }

    public function cancel(Transaction $transaction)
    {
        Gate::authorize('cancel', $transaction);

        if (!$transaction->cancel()) {
            return response()->json(['message' => 'Transaction cannot be cancelled'], 422);
        }

        return response()->json(['message' => 'Transaction cancelled successfully']);
    }
}
