<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Store::class);
        // dd($request->user()->organizations()->first());
        // Your index logic
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

        $store = Store::create($validated);
        // dd('cp');

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

        $store->items()->syncWithoutDetaching([
            $validated['item_id'] => ['quantity' => $validated['quantity']]
        ]);

        return response()->json(['message' => 'Inventory updated successfully']);
    }

    public function getStoreItems(Store $store)
    {
        Gate::authorize('view', $store);

        $items = $store->items()->withPivot('quantity')->get();

        return response()->json($items);
    }
}
