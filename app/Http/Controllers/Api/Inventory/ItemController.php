<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Item::class);

        $items = Item::where('organization_id', $request->user()->currentOrganization->id)
            ->with('stores')
            ->get();

        return response()->json($items);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Item::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $item = Item::create($validated);

        return response()->json($item, 201);
    }

    public function show(Item $item)
    {
        Gate::authorize('view', $item);

        $item->load('stores', 'transactionItems');

        return response()->json($item);
    }

    public function update(Request $request, Item $item)
    {
        Gate::authorize('update', $item);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'sku' => 'sometimes|string|unique:inventory_items,sku,' . $item->id,
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'unit' => 'sometimes|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function destroy(Item $item)
    {
        Gate::authorize('delete', $item);

        $item->delete();

        return response()->json(null, 204);
    }
}
