<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class InventoryDashboardController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        $organization = Organization::find($user->current_organization_id);

        if (! $organization) {
            return redirect('/setup');
        }

        $stores = Store::forOrganization($organization->id)->withCount('items')->get();
        $totalItems = Item::where('organization_id', $organization->id)->count();
        $lowStockItems = $this->getLowStockItems($organization);

        $recentTransactions = Transaction::whereIn('store_id', Store::forOrganization($organization->id)->pluck('id'))
            ->with('store')
            ->latest()
            ->take(5)
            ->get();

        return view('inventory.dashboard', compact(
            'organization',
            'stores',
            'totalItems',
            'lowStockItems',
            'recentTransactions'
        ));
    }

    protected function getLowStockItems(Organization $organization): Collection
    {
        $lowStockAlerts = [];

        $items = Item::where('organization_id', $organization->id)->get();

        foreach ($items as $item) {
            foreach ($item->stores as $store) {
                if ($store->pivot->quantity <= $item->reorder_level) {
                    $lowStockAlerts[] = $item;

                    break;
                }
            }
        }

        return collect($lowStockAlerts);
    }
}
