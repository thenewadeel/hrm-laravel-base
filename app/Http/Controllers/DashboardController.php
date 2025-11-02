<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Inventory\Store;
use App\Models\Inventory\Item;
use App\Models\Inventory\Transaction;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $organization = Organization::find($user->operating_organization_id);

        // Redirect to setup if no organization
        if (!$organization) {
            return redirect('/setup');
        }

        // Get dashboard data
        $stores = Store::withCount('items')->get();
        $totalItems = Item::count();
        $lowStockItems = Item::lowInStock()
            ->get();

        $recentTransactions = Transaction::whereIn('store_id', Store::forOrganization($organization->id)->pluck('id'))
            ->with('store')
            ->latest()
            ->take(5)
            ->get();

        $items = Item::pluck('id', 'name');
        // dd([
        //     'user' => $user->operating_organization_id,
        //     'role' => $user->getAllRoles(),
        //     'permissions' => $user->getAllPermissions(),
        //     'organization' => $organization->id,
        //     'stores' => $stores->first()->organization->id,
        //     'totalItems' => $totalItems,
        //     'lowStockItems' => $lowStockItems,
        //     'recentTransactions' => $recentTransactions
        // ]);
        return view('dashboard', compact(
            'organization',
            'stores',
            'totalItems',
            'lowStockItems',
            'recentTransactions'
        ));
    }

    protected function getLowStockItems(Organization $organization)
    {
        // Get items that have store quantities below reorder level
        return Item::where('organization_id', $organization->id)
            ->whereHas('stores', function ($query) {
                $query->whereColumn('inventory_store_items.quantity', '<=', 'inventory_items.reorder_level');
            })
            ->with(['stores' => function ($query) {
                $query->whereColumn('inventory_store_items.quantity', '<=', 'inventory_items.reorder_level');
            }])
            ->get()
            ->map(function ($item) {
                // Get the low stock store quantities
                $lowStockStores = $item->stores->map(function ($store) {
                    return [
                        'store_name' => $store->name,
                        'quantity' => $store->pivot->quantity,
                        'reorder_level' => $item->reorder_level
                    ];
                });

                return [
                    'item' => $item,
                    'low_stock_stores' => $lowStockStores
                ];
            })
            ->flatten(1);
    }
}
