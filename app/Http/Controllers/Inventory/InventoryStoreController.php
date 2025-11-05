<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InventoryStoreController extends Controller
{
    /**
     * Display a listing of inventory stores.
     */
    public function index(Request $request): View
    {
        $query = Store::with(['organization_unit']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%')
                    ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $stores = $query->latest()->paginate(20);

        return view('inventory.stores.index', compact('stores'));
    }

    /**
     * Show the form for creating a new inventory store.
     */
    public function create(): View
    {
        $organizationUnits = OrganizationUnit::with('organization')->get();
        return view('inventory.stores.form', compact('organizationUnits'));
    }

    /**
     * Store a newly created inventory store.
     */
    public function store(Request $request): RedirectResponse
    {
        // dd([$request->all()]);
        $validated = $request->validate([
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inventory_stores,code',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);
        $store = Store::create($validated);
        // dd($store);
        return redirect()->route('inventory.stores.index')
            ->with('success', 'Store created successfully.');
    }

    /**
     * Display the specified inventory store.
     */
    public function show(Store $store): View
    {
        $store->loadCount(['items', 'transactions'])
            ->load(['organization_unit', 'items', 'transactions']);

        // Execute the query with get()
        $recentTransactions = $store->transactions()
            ->latest()
            ->take(5)
            ->get();

        $lowStockItems = $store->items()->lowInStock()->get();

        // Calculate counts for the view
        $lowStockCount = $store->items()->lowInStock()->count();
        $outOfStockCount = $store->items()->outOfStock()->count();
        $activeItemsCount = $store->items()->active()->count();
        $inactiveItemsCount = $store->items()->inActive()->count();

        // Calculate total value (assuming you have price in items)
        $totalValue = $store->items->sum(function ($item) {
            return ($item->pivot->quantity ?? 0) * ($item->unit_price ?? 0);
        });

        return view('inventory.stores.show', compact(
            'store',
            'recentTransactions',
            'lowStockItems',
            'lowStockCount',
            'outOfStockCount',
            'activeItemsCount',
            'inactiveItemsCount',
            'totalValue'
        ));
    }

    /**
     * Show the form for editing the specified inventory store.
     */
    public function edit(Store $store): View
    {
        $organizationUnits = OrganizationUnit::with('organization')->get();
        return view('inventory.stores.form', compact('store', 'organizationUnits'));
    }

    /**
     * Update the specified inventory store.
     */
    public function update(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:inventory_stores,code,' . $store->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $store->update($validated);

        return redirect()->route('inventory.stores.index')
            ->with('success', 'Store updated successfully.');
    }

    /**
     * Remove the specified inventory store.
     */
    public function destroy(Store $store): RedirectResponse
    {
        // Check if store has transactions or items before deleting
        if ($store->transactions()->exists() || $store->items()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete store that has associated transactions or items.');
        }

        $store->delete();

        return redirect()->route('inventory.stores.index')
            ->with('success', 'Store deleted successfully.');
    }
}
