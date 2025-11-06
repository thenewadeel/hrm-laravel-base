<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Inventory\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryReportController extends Controller
{
    /**
     * Display the inventory reports dashboard.
     */
    public function index(): View
    {
        $user = auth()->user();
        // $organization = $user->organization;
        $stores = Store::all();
        return view('inventory.reports.index', compact('stores'));
    }

    /**
     * Display low stock report.
     */
    /**
     * Display low stock report.
     */
    public function lowStock(Request $request): View
    {
        // Get filter parameters
        $storeId = $request->get('store_id');
        $category = $request->get('category');
        $severity = $request->get('severity');

        // Base query for items in the current organization
        $itemsQuery = Item::query()
            ->with(['stores' => function ($query) use ($storeId) {
                if ($storeId) {
                    $query->where('inventory_stores.id', $storeId);
                }
            }])
            ->where('organization_id', auth()->user()->operatingOrganizationId)
            ->where('is_active', true);

        // Apply category filter
        if ($category) {
            $itemsQuery->where('category', $category);
        }

        // Get all items with their store quantities
        $items = $itemsQuery->get()->map(function ($item) use ($storeId) {
            // Calculate total quantity across all stores or specific store
            if ($storeId) {
                $storeItem = $item->stores->firstWhere('id', $storeId);
                $item->total_quantity = $storeItem ? $storeItem->pivot->quantity : 0;
                $item->store = $storeItem;
            } else {
                $item->total_quantity = $item->stores->sum('pivot.quantity');
                $item->store = $item->stores->first();
            }

            return $item;
        });

        // Filter out-of-stock items (quantity = 0)
        $outOfStockItems = $items->filter(function ($item) {
            return $item->total_quantity <= 0;
        });
        // Filter low stock items (quantity > 0 but below reorder level)
        $lowStockItems = $items->filter(function ($item) {
            return $item->total_quantity > 0 && $item->total_quantity <= $item->reorder_level;
        });

        // Apply severity filter if specified
        if ($severity) {
            if ($severity === 'critical') {
                $lowStockItems = collect(); // Only show out of stock
            } elseif ($severity === 'warning') {
                $outOfStockItems = collect(); // Only show low stock
            } elseif ($severity === 'info') {
                // Show items near reorder level (within 20%)
                $lowStockItems = $items->filter(function ($item) {
                    $threshold = $item->reorder_level * 1.2;
                    return $item->total_quantity > $item->reorder_level && $item->total_quantity <= $threshold;
                });
                $outOfStockItems = collect();
            }
        }

        // Generate reorder suggestions
        $reorderSuggestions = $items->filter(function ($item) {
            return $item->total_quantity <= $item->reorder_level;
        })->map(function ($item) {
            $suggestedOrder = max(
                $item->reorder_level - $item->total_quantity + 10, // Base suggestion + buffer
                $item->min_order_quantity ?? 1 // Minimum order quantity if set
            );

            return [
                'item' => $item,
                'current' => $item->total_quantity,
                'reorder_level' => $item->reorder_level,
                'suggested_order' => $suggestedOrder,
            ];
        })->values();

        // Get stores for filter dropdown
        $stores = Store::where('is_active', true)
            ->whereHas('organization_unit', function ($query) {
                $query->where('organization_id', auth()->user()->operatingOrganizationId);
            })
            ->get();

        // Get unique categories for filter dropdown
        $categories = Item::where('organization_id', auth()->user()->operatingOrganizationId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();
        $returnBag = compact(
            'outOfStockItems',
            'lowStockItems',
            'reorderSuggestions',
            'stores',
            'categories',
            'storeId',
            'category',
            'severity'
        );
        // dd([$returnBag]);
        return view('inventory.reports.low-stock', $returnBag);
    }

    /**
     * Display stock movement report.
     */
    /**
     * Display stock movement report.
     */
    public function movement(Request $request): View
    {
        // Get filter parameters with defaults
        $startDate = $request->get('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $itemId = $request->get('item_id');
        $transactionType = $request->get('transaction_type');

        // Base query for transaction items within date range
        $movementsQuery = \App\Models\Inventory\TransactionItem::query()
            ->with(['transaction.store', 'item'])
            ->whereHas('transaction', function ($query) use ($startDate, $endDate, $transactionType) {
                $query->where('status', 'completed');
                $query->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59']);

                if ($transactionType) {
                    $query->where('type', $transactionType);
                }

                // Scope to user's organization
                $query->whereHas('store.organization_unit', function ($q) {
                    $q->where('organization_id', auth()->user()->operatingOrganizationId);
                });
            });

        // Apply item filter
        if ($itemId) {
            $movementsQuery->where('item_id', $itemId);
        }

        // Get paginated movements
        $movements = $movementsQuery->orderBy('transaction_date', 'desc')
            ->paginate(25)
            ->appends($request->query());

        // Calculate summary statistics
        $summary = $this->calculateMovementSummary($startDate, $endDate, $itemId, $transactionType);

        // Get top received and issued items
        // $topReceived = $this->getTopMovers($startDate, $endDate, 'receipt', 5);
        // $topIssued = $this->getTopMovers($startDate, $endDate, 'issue', 5);
        $topReceived = $this->getTopReceived($startDate, $endDate, 5);
        $topIssued = $this->getTopIssued($startDate, $endDate, 5);

        // Get data for filters
        $allItems = \App\Models\Inventory\Item::where('organization_id', auth()->user()->operatingOrganizationId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = \App\Models\Inventory\Store::where('is_active', true)
            // ->forOrganization(auth()->user()->operatingOrganizationId)
            ->get();
        // dd([
        //     'movements' => $movements->toArray(),
        //     'summary' =>            $summary,
        //     'topReceived' =>            $topReceived->toArray(),
        //     'topIssued' =>            $topIssued->toArray(),
        //     'allItems' =>            $allItems->toArray(),
        //     'stores' =>            $stores->toArray(),
        //     'startDate' =>            $startDate,
        //     'endDate' =>            $endDate,
        //     'itemId' =>            $itemId,
        //     'transactionType' =>            $transactionType
        // ]);
        return view('inventory.reports.movement', compact(
            'movements',
            'summary',
            'topReceived',
            'topIssued',
            'allItems',
            'stores',
            'startDate',
            'endDate',
            'itemId',
            'transactionType'
        ));
    }

    /**
     * Calculate movement summary statistics.
     */
    private function calculateMovementSummary(string $startDate, string $endDate, ?int $itemId = null, ?string $transactionType = null): array
    {
        $baseQuery = \App\Models\Inventory\TransactionItem::query()
            ->whereHas('transaction', function ($query) use ($startDate, $endDate, $transactionType) {
                $query->where('status', 'completed');
                $query->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59']);

                if ($transactionType) {
                    $query->where('type', $transactionType);
                }

                $query->whereHas('store.organization_unit', function ($q) {
                    $q->where('organization_id', auth()->user()->operatingOrganizationId);
                });
            });

        if ($itemId) {
            $baseQuery->where('item_id', $itemId);
        }

        // Total received (receipt transactions)
        $totalReceived = (clone $baseQuery)
            ->whereHas('transaction', function ($query) {
                $query->where('type', 'receipt');
            })
            ->sum('quantity');

        // Total issued (issue transactions)
        $totalIssued = (clone $baseQuery)
            ->whereHas('transaction', function ($query) {
                $query->whereIn('type', ['issue', 'transfer']);
            })
            ->sum('quantity');

        // Total transactions count
        $totalTransactions = \App\Models\Inventory\Transaction::query()
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59'])
            ->whereHas('store.organization_unit', function ($query) {
                $query->where('organization_id', auth()->user()->operatingOrganizationId);
            })
            ->when($transactionType, function ($query, $type) {
                $query->where('type', $type);
            })
            ->count();

        return [
            'total_received' => $totalReceived,
            'total_issued' => $totalIssued,
            'net_movement' => $totalReceived - $totalIssued,
            'total_transactions' => $totalTransactions,
        ];
    }

    /**
     * Get top moving items (received or issued).
     */
    private function getTopMovers(string $startDate, string $endDate, string $type, int $limit = 5)
    {
        $query = \App\Models\Inventory\TransactionItem::query()
            ->selectRaw('
            items.id,
            items.name,
            items.sku,
            items.unit,
            SUM(
                CASE
                    WHEN transactions.type IN ("receipt", "adjustment") AND transaction_items.quantity > 0 THEN transaction_items.quantity
                    WHEN transactions.type IN ("issue", "transfer") THEN -ABS(transaction_items.quantity)
                    ELSE 0
                END
            ) as total_quantity
        ')
            ->join('inventory_items as items', 'transaction_items.item_id', '=', 'items.id')
            ->join('inventory_transactions as transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate . ' 23:59:59'])
            ->whereHas('transaction.store.organization_unit', function ($q) {
                $q->where('organization_id', auth()->user()->operatingOrganizationId);
            })
            ->groupBy('items.id', 'items.name', 'items.sku', 'items.unit');

        // Apply type-specific filtering
        if ($type === 'receipt') {
            $query->where('transactions.type', 'receipt');
        } elseif ($type === 'issue') {
            $query->where('transactions.type', 'issue');
        } elseif ($type === 'transfer') {
            // For transfers, we might want to show net movement or separate out/in
            $query->where('transactions.type', 'transfer');
            // Or if you want net transfer movement:
            // $query->whereIn('transactions.type', ['transfer']);
        }

        return $query->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }


    private function getTopReceived(string $startDate, string $endDate, int $limit = 5)
    {
        $items = \App\Models\Inventory\TransactionItem::with(['item', 'transaction'])
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                    ->where('type', 'receipt')
                    ->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59'])
                    ->whereHas('store.organization_unit', function ($q) {
                        $q->where('organization_id', auth()->user()->operatingOrganizationId);
                    });
            })
            ->get()
            ->groupBy('item_id')
            ->map(function ($transactionItems, $itemId) {
                $item = $transactionItems->first()->item;
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'unit' => $item->unit,
                    'total_quantity' => $transactionItems->sum('quantity')
                ];
            })
            ->sortByDesc('total_quantity')
            ->take($limit)
            ->values();

        return $items;
    }

    private function getTopIssued(string $startDate, string $endDate, int $limit = 5)
    {
        $items = \App\Models\Inventory\TransactionItem::with(['item', 'transaction'])
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->where('status', 'completed')
                    ->where('type', 'issue')
                    ->whereBetween('transaction_date', [$startDate, $endDate . ' 23:59:59'])
                    ->whereHas('store.organization_unit', function ($q) {
                        $q->where('organization_id', auth()->user()->operatingOrganizationId);
                    });
            })
            ->get()
            ->groupBy('item_id')
            ->map(function ($transactionItems, $itemId) {
                $item = $transactionItems->first()->item;
                return (object) [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'unit' => $item->unit,
                    'total_quantity' => $transactionItems->sum('quantity')
                ];
            })
            ->sortByDesc('total_quantity')
            ->take($limit)
            ->values();

        return $items;
    }

    /**
     * Get movement data for charts (optional).
     */
    private function getMovementChartData(string $startDate, string $endDate)
    {
        return \App\Models\Inventory\TransactionItem::query()
            ->selectRaw('DATE(transactions.transaction_date) as date,
                     transactions.type,
                     SUM(transaction_items.quantity) as total_quantity')
            ->join('inventory_transactions as transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate . ' 23:59:59'])
            ->whereHas('transaction.store.organization_unit', function ($query) {
                $query->where('organization_id', auth()->user()->operatingOrganizationId);
            })
            ->groupBy('date', 'transactions.type')
            ->orderBy('date')
            ->get()
            ->groupBy('type');
    }
    /**
     * Display out of stock report
     */
    public function outOfStock(Request $request): View
    {
        $query = Item::outOfStock()->with(['stores']);

        if ($request->has('store_id') && $request->store_id) {
            $query->whereHas('stores', function ($q) use ($request) {
                $q->where('inventory_stores.id', $request->store_id);
            });
        }

        $items = $query->paginate(20);
        $stores = Store::where('is_active', true)->get();

        return view('inventory.reports.out-of-stock', compact('items', 'stores'));
    }

    /**
     * Display stock levels report - FIXED VERSION
     */
    public function stockLevels(Request $request): View
    {
        $query = Item::with(['stores', 'stores' => function ($query) {
            $query->withPivot(['quantity', 'min_stock', 'max_stock']);
        }])->where('is_active', true);

        // dd([$query->get(), 'items' => Item::get()]);
        // Filter by store
        if ($request->has('store_id') && $request->store_id) {
            $query->where('store_id', $request->store_id);
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by stock status (matching blade values)
        if ($request->has('status') && $request->status) {
            switch ($request->status) {
                case 'low_stock':
                    $query->whereHas('stores', function ($q) {
                        $q->whereRaw('inventory_store_items.quantity <= inventory_store_items.min_stock')
                            ->where('inventory_store_items.quantity', '>', 0);
                    });
                    break;
                case 'out_of_stock':
                    $query->whereHas('stores', function ($q) {
                        $q->where('inventory_store_items.quantity', '<=', 0);
                    });
                    break;
                case 'in_stock':
                    $query->whereHas('stores', function ($q) {
                        $q->whereRaw('inventory_store_items.quantity > inventory_store_items.min_stock')
                            ->where('inventory_store_items.quantity', '>', 0);
                    });
                    break;
            }
        }

        // Get paginated items
        $items = $query->paginate(20);

        // Get stores and categories for filters
        $stores = Store::where('is_active', true)->get();
        $categories = Item::distinct()->pluck('category')->filter();

        // Calculate summary statistics
        $totalItems = Item::where('is_active', true)->count();

        $lowStockCount = Item::where('is_active', true)
            ->whereHas('stores', function ($q) {
                $q->whereRaw('inventory_store_items.quantity <= inventory_store_items.min_stock')
                    ->where('inventory_store_items.quantity', '>', 0);
            })->count();

        $outOfStockCount = Item::where('is_active', true)
            ->whereHas('stores', function ($q) {
                $q->where('inventory_store_items.quantity', '<=', 0);
            })->count();

        // Calculate total inventory value
        $totalValue = Item::where('is_active', true)
            ->with(['stores' => function ($query) {
                $query->withPivot('quantity');
            }])
            ->get()
            ->sum(function ($item) {
                return ($item->cost_price ?? 0) * ($item->total_quantity ?? 0);
            });

        // Get low stock items for alerts
        $lowStockItems = Item::where('is_active', true)
            ->whereHas('stores', function ($q) {
                $q->whereRaw('inventory_store_items.quantity <= inventory_store_items.min_stock')
                    ->where('inventory_store_items.quantity', '>', 0);
            })
            ->with('store')
            ->get();

        return view('inventory.reports.stock-levels', compact(
            'items',
            'stores',
            'categories',
            'totalItems',
            'lowStockCount',
            'outOfStockCount',
            'totalValue',
            'lowStockItems'
        ));
    }
}
