<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $organization = auth()->user()->currentOrganization;

        if (!$organization) {
            return redirect()->route('setup.welcome');
        }

        $totalItems = Item::where('organization_id', $organization->id)->count();
        $lowStockItems = Item::where('organization_id', $organization->id)
            ->where('quantity', '<=', DB::raw('reorder_level'))
            ->where('quantity', '>', 0)
            ->count();
        $outOfStockItems = Item::where('organization_id', $organization->id)
            ->where('quantity', '<=', 0)
            ->count();
        $totalValue = Item::where('organization_id', $organization->id)
            ->sum(DB::raw('quantity * cost_price'));

        $recentTransactions = Transaction::with(['store', 'createdBy'])
            ->whereHas('store', function ($query) use ($organization) {
                $query->where('organization_id', $organization->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $storeSummary = Store::withCount('items')
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->get();

        return view('dashboard', compact(
            'totalItems',
            'lowStockItems',
            'outOfStockItems',
            'totalValue',
            'recentTransactions',
            'storeSummary'
        ));
    }

    protected function getLowStockItems($organization)
    {
        return Item::where('organization_id', $organization->id)
            ->where('quantity', '<=', DB::raw('reorder_level'))
            ->where('quantity', '>', 0)
            ->with(['stores', 'head'])
            ->orderBy('quantity', 'asc')
            ->get();
    }
}
