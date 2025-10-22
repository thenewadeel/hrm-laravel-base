<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Store;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class StoreController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Store::class);
        $stores = Store::where('organization_id', $request->user()->organizations()->first()->id)
            ->withCount('items')
            ->get();

        return response()->json($stores);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Store::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inventory_stores,code',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'organization_unit_id' => 'required|exists:organization_units,id'
        ]);

        $store = $this->inventoryService->createStore($validated, $request->user());

        return response()->json($store, 201);
    }

    public function show(Store $store)
    {
        Gate::authorize('view', $store);
        $store->load('items', 'organization');

        return response()->json($store);
    }

    public function update(Request $request, Store $store)
    {
        Gate::authorize('update', $store);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:inventory_stores,code,' . $store->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean'
        ]);

        $store->update($validated);
        return response()->json($store);
    }
    public function destroy(Store $store)
    {
        Gate::authorize('delete', $store);

        $store->delete();

        return response()->json(null, 204);
    }
    public function updateInventory(Request $request, Store $store)
    {
        Gate::authorize('manageInventory', $store);
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:0'
        ]);

        $item = \App\Models\Inventory\Item::find($validated['item_id']);
        $this->inventoryService->updateStoreInventory($store, $item, $validated['quantity'], $request->user());

        return response()->json(['message' => 'Inventory updated successfully']);
    }

    public function stockLevels(Store $store)
    {
        Gate::authorize('view', $store);
        $stockLevels = $this->inventoryService->getStoreStockLevels($store, request()->user());
        return response()->json($stockLevels);
    }
}
