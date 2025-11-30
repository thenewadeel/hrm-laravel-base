<?php

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\Scopes\StoreOrganizationScope;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $organization = Organization::find($user->current_organization_id);

        // Redirect to setup if no organization
        if (! $organization) {
            return redirect('/setup');
        }

        // Get dashboard data
        $stores = Store::forOrganization($organization->id)->withCount('items')->get();
        $totalItems = Item::where('organization_id', $organization->id)->count();

        // Simple low stock items query
        $lowStockItems = collect();
        $items = Item::where('organization_id', $organization->id)->get();

        foreach ($items as $item) {
            foreach ($item->stores as $store) {
                if ($store->pivot->quantity <= $item->reorder_level) {
                    $lowStockItems->push($item);
                    break; // Only add each item once
                }
            }
        }

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
        //     'organizationU' => OrganizationUnit::ofOrganization($organization->id),
        //     'stores' => $stores,
        //     'storesall' => Store::withoutGlobalScope(StoreOrganizationScope::class)->get()->toArray(),
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
        $lowStockAlerts = [];

        // Get items for the organization
        $items = Item::where('organization_id', $organization->id)->get();

        foreach ($items as $item) {
            // Get stores where this item has low stock
            $lowStockStores = [];

            foreach ($item->stores as $store) {
                if ($store->pivot->quantity <= $item->reorder_level) {
                    $lowStockStores[] = [
                        'store_name' => $store->name,
                        'quantity' => $store->pivot->quantity,
                        'reorder_level' => $item->reorder_level,
                    ];
                }
            }

            // Only add item if it has low stock in some store
            if (! empty($lowStockStores)) {
                $lowStockAlerts[] = [
                    'item' => $item,
                    'low_stock_stores' => collect($lowStockStores),
                ];
            }
        }

        return collect($lowStockAlerts);
    }
}
