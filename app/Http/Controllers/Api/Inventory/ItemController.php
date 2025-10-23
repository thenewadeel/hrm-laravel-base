<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Http\Resources\ItemResource;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ItemController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Item::class);

        $query = Item::where('organization_id', $request->user()->organizations()->first()->id)
            ->with('stores');

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && !empty($request->category)) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort functionality
        $sortField = $request->get('sort_field', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');

        if (in_array($sortField, ['name', 'sku', 'category', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $items = $query->paginate($request->get('per_page', 15));

        return ItemResource::collection($items);
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
            'organization_id' => 'required|exists:organizations,id',
            // 'organization_unit_id' => 'required|exists:organization_units,id'
        ]);

        $item = Item::create($validated);

        return new ItemResource($item->load('stores'));
    }

    public function show(Item $item)
    {
        Gate::authorize('view', $item);

        return new ItemResource($item->load('stores', 'transactionItems'));
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

        return new ItemResource($item->load('stores'));
    }

    public function destroy(Item $item)
    {
        Gate::authorize('delete', $item);

        $item->delete();

        return response()->json(null, 204);
    }

    /**
     * Get item availability across all stores
     */
    public function availability(Item $item, Request $request)
    {
        Gate::authorize('view', $item);

        $availability = $this->inventoryService->getItemAvailability($item, $request->user());

        return response()->json([
            'success' => true,
            'data' => $availability
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request)
    {
        Gate::authorize('viewAny', Item::class);

        $query = Item::where('organization_id', $request->user()->organizations()->first()->id)
            ->whereHas('stores', function ($q) {
                $q->where('inventory_store_items.quantity', '<=', \DB::raw('items.reorder_level'))
                    ->where('inventory_store_items.quantity', '>', 0);
            })
            ->with(['stores' => function ($query) {
                $query->wherePivot('quantity', '<=', \DB::raw('items.reorder_level'));
            }]);

        $lowStockItems = $query->paginate($request->get('per_page', 15));

        return ItemResource::collection($lowStockItems);
    }

    /**
     * Get out of stock items
     */
    public function outOfStock(Request $request)
    {
        Gate::authorize('viewAny', Item::class);

        $query = Item::where('organization_id', $request->user()->organizations()->first()->id)
            ->where(function ($q) {
                $q->whereHas('stores', function ($subQuery) {
                    $subQuery->where('inventory_store_items.quantity', '<=', 0);
                })->orDoesntHave('stores');
            })
            ->with('stores');

        $outOfStockItems = $query->paginate($request->get('per_page', 15));

        return ItemResource::collection($outOfStockItems);
    }
}
