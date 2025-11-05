<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Head;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request): View
    {
        $query = Item::with(['organization', 'head']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'low_stock') {
                $query->lowInStock();
            }
        }

        $items = $query->latest()->paginate(20);
        $categories = Item::distinct()->pluck('category')->filter();
        $stores = Store::where('is_active', true)->get();

        return view('inventory.items.index', compact('items', 'categories', 'stores'));
    }

    /**
     * Show the form for creating a new inventory item.
     */
    public function create(): View
    {
        $organizations = Organization::where('is_active', true)->get();
        $heads = Head::where('is_active', true)->get();
        return view('inventory.items.form', compact('organizations', 'heads'));
    }

    /**
     * Store a newly created inventory item.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Item::create($validated);

        return redirect()->route('inventory.items.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified inventory item.
     */
    public function show(Item $item): View
    {
        $item->load(['organization', 'head', 'stores']);
        return view('inventory.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified inventory item.
     */
    public function edit(Item $item): View
    {
        $organizations = Organization::where('is_active', true)->get();
        $heads = Head::where('is_active', true)->get();
        return view('inventory.items.form', compact('item', 'organizations', 'heads'));
    }

    /**
     * Update the specified inventory item.
     */
    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:inventory_items,sku,' . $item->id,
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()->route('inventory.items.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified inventory item.
     */
    public function destroy(Item $item): RedirectResponse
    {
        // Check if item has transactions before deleting
        if ($item->transactions()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete item that has associated transactions.');
        }

        $item->delete();

        return redirect()->route('inventory.items.index')
            ->with('success', 'Item deleted successfully.');
    }
}
