<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display the inventory dashboard.
     */
    public function index(): View
    {
        $stats = $this->getDashboardStats();

        // If your view needs lowStockItems, fetch them properly
        $lowStockItems = Item::lowInStock()
            // ->with(['stores' => function ($query) {
            //     $query->withPivot(['quantity', 'min_stock']);
            // }])
            ->limit(10)
            ->get();

        return view('inventory.index', compact('stats', 'lowStockItems'));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats(): array
    {
        return [
            'total_items' => Item::count(),
            'total_stores' => Store::count(),
            'low_stock_items' => $this->getLowStockItemsCount(),
            'recent_transactions' => Transaction::latest()->take(5)->count(),
            'total_transactions_today' => Transaction::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * Get count of low stock items across all stores
     */
    private function getLowStockItemsCount(): int
    {
        return \DB::table('inventory_store_items')
            ->whereRaw('quantity < min_stock')
            ->where('quantity', '>', 0)
            ->count();
    }
}
