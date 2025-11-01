<?php

namespace App\Http\Controllers\Api\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Http\Resources\StoreResource;
use App\Permissions\InventoryPermissions;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StoreController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        // dd([
        //     $request->all(),
        //     $request->user()->id,
        //     $user->id,
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     // $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION),
        //     // $user->hasRole(OrganizationRoles::SUPER_ADMIN)
        // ]);
        Gate::authorize('viewAny', Store::class);

        $organizationId = $request->has('organization_id') && !empty($request->organization_id)
            ? $request->organization_id
            : $request->user()->current_organization_id;

        $stores = Store::with(['organization_unit.organization'])
            ->withCount('items')
            ->forOrganization($organizationId)
            ->search($request->search)
            ->active($request->boolean('is_active', true))
            ->when($request->has('sort_field'), function ($query) use ($request) {
                $sortField = $request->get('sort_field', 'name');
                $sortDirection = $request->get('sort_direction', 'asc');

                if (in_array($sortField, ['name', 'code', 'location', 'created_at'])) {
                    $query->orderBy($sortField, $sortDirection);
                }
            })
            ->paginate($request->get('per_page', 15));

        return StoreResource::collection($stores);
    }
    public function store(Request $request)
    {
        Gate::authorize('create', Store::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inventory_stores,code',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            // 'organization_id' => 'required|exists:organizations,id',
            'organization_unit_id' => 'required|exists:organization_units,id'
        ]);

        $store = Store::create($validated);

        return new StoreResource($store);
    }

    public function show(Store $store)
    {
        $user = auth()->user();
        // dd([
        //     $store,
        //     $user->id,
        //     $user->organizations[0]->id,
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     $user->hasPermission(InventoryPermissions::VIEW_STORES, $store->organization) &&
        //         $user->organizations->contains($store->organization->id)
        // ]);
        Gate::authorize('view', $store);

        return new StoreResource($store->load('items', 'organization'));
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

        return new StoreResource($store);
    }

    public function destroy(Store $store)
    {
        Gate::authorize('delete', $store);

        $store->delete();

        return response()->json(null, 204);
    }

    /**
     * Add item to store or update quantity
     */
    public function addItem(Store $store, Request $request)
    {
        Gate::authorize('manageInventory', $store);

        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0'
        ]);

        $item = Item::findOrFail($validated['item_id']);

        // Check if item belongs to same organization
        if ($item->organization_id !== $store->organization->id) {
            return response()->json([
                'message' => 'Item does not belong to the same organization'
            ], 422);
        }

        $this->inventoryService->updateStoreInventory(
            $store,
            $item,
            $validated['quantity'],
            $request->user(),
            $validated['min_stock'] ?? null,
            $validated['max_stock'] ?? null
        );

        return response()->json([
            'message' => 'Item added to store successfully',
            'data' => [
                'store_id' => $store->id,
                'item_id' => $item->id,
                'quantity' => $validated['quantity']
            ]
        ], 201);
    }

    /**
     * Update item quantity in store
     */
    public function updateItemQuantity(Store $store, Item $item, Request $request)
    {
        Gate::authorize('manageInventory', $store);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0'
        ]);

        // Check if item belongs to same organization
        if ($item->organization_id !== $store->organization->id) {
            return response()->json([
                'message' => 'Item does not belong to the same organization'
            ], 422);
        }

        $this->inventoryService->updateStoreInventory(
            $store,
            $item,
            $validated['quantity'],
            $request->user(),
            $validated['min_stock'] ?? null,
            $validated['max_stock'] ?? null
        );

        return response()->json([
            'message' => 'Item quantity updated successfully',
            'data' => [
                'store_id' => $store->id,
                'item_id' => $item->id,
                'quantity' => $validated['quantity']
            ]
        ]);
    }

    /**
     * Remove item from store
     */
    public function removeItem(Store $store, Item $item)
    {
        Gate::authorize('manageInventory', $store);

        // Check if item belongs to same organization
        if ($item->organization_id !== $store->organization->id) {
            return response()->json([
                'message' => 'Item does not belong to the same organization'
            ], 422);
        }

        $store->items()->detach($item->id);

        return response()->json([
            'message' => 'Item removed from store successfully'
        ]);
    }

    /**
     * Get store items with quantities
     */
    public function getStoreItems(Store $store)
    {
        Gate::authorize('view', $store);

        $items = $store->items()
            ->withPivot('quantity', 'min_stock', 'max_stock')
            ->paginate(request()->get('per_page', 15));

        return response()->json([
            'data' => $items
        ]);
    }

    /**
     * Get store stock levels
     */
    public function stockLevels(Store $store)
    {
        Gate::authorize('view', $store);

        $stockLevels = $this->inventoryService->getStoreStockLevels($store, request()->user());

        return response()->json([
            'data' => $stockLevels
        ]);
    }
}
