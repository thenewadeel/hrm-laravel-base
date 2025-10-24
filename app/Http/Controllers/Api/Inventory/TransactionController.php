<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Transaction;
use App\Http\Resources\TransactionResource;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TransactionController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Transaction::class);

        $query = Transaction::with(['store', 'createdBy', 'items.item'])
            ->whereHas('store', function ($query) use ($request) {
                $query->forOrganization($request->user()->organizations()->first()->id);
            });

        // Filter by status
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        // Search by reference
        if ($request->has('search') && !empty($request->search)) {
            $query->where('reference', 'like', "%{$request->search}%");
        }

        // Date range filter
        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->where('transaction_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->where('transaction_date', '<=', $request->to_date);
        }

        // Sort functionality
        $sortField = $request->get('sort_field', 'transaction_date');
        $sortDirection = $request->get('sort_direction', 'desc');

        if (in_array($sortField, ['reference', 'transaction_date', 'created_at', 'status'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return TransactionResource::collection($transactions);
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
            'items' => 'sometimes|array', // ✅ Change from 'required' to 'sometimes'
            'items.*.item_id' => 'required_with:items|exists:inventory_items,id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.notes' => 'nullable|string'
        ]);

        $transaction = $this->inventoryService->createTransaction($validated, $request->user());

        return response()->json([
            'data' => new TransactionResource($transaction->load(['store', 'items.item', 'createdBy']))
        ], 201);
    }

    public function show(Transaction $transaction)
    {
        Gate::authorize('view', $transaction);

        $transaction->load(['store', 'items.item', 'createdBy', 'approvedBy']);

        return new TransactionResource($transaction);
    }

    public function update(Request $request, Transaction $transaction)
    {
        Gate::authorize('update', $transaction);

        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'sometimes|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string'
        ]);

        $transaction = $this->inventoryService->updateTransaction($transaction, $validated, $request->user());

        if (isset($validated['items'])) {
            $transaction->items()->delete();
            foreach ($validated['items'] as $item) {
                $transaction->items()->create([
                    'item_id' => $item['item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }
        }
        return new TransactionResource($transaction->load(['store', 'items.item', 'createdBy']));
    }

    public function destroy(Transaction $transaction)
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return response()->json(null, 204);
    }

    /**
     * Add items to a draft transaction
     */
    public function addItems(Transaction $transaction, Request $request)
    {
        // dd($request->user()->getAllPermissions());
        Gate::authorize('update', $transaction);

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string'
        ]);

        $transaction = $this->inventoryService->addItemsToTransaction($transaction, $validated['items'], $request->user());

        return response()->json([
            'message' => 'Items added to transaction successfully',
            'data' => new TransactionResource($transaction->load(['store', 'items.item', 'createdBy']))
        ]);
    }

    public function finalize(Transaction $transaction)
    {
        Gate::authorize('finalize', $transaction);

        $transaction = $this->inventoryService->finalizeTransaction($transaction, request()->user());

        return response()->json([
            'message' => 'Transaction finalized successfully',
            'data' => new TransactionResource($transaction->load(['store', 'items.item', 'createdBy', 'approvedBy']))
        ]);
    }

    public function cancel(Transaction $transaction)
    {
        Gate::authorize('cancel', $transaction);

        $transaction = $this->inventoryService->cancelTransaction($transaction, request()->user());

        return response()->json([
            'message' => 'Transaction cancelled successfully',
            'data' => new TransactionResource($transaction->load(['store', 'items.item', 'createdBy']))
        ]);
    }
}
