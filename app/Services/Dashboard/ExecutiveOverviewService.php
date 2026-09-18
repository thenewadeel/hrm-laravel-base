<?php

namespace App\Services\Dashboard;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\Voucher;
use App\Models\AttendanceRecord;
use App\Models\Inventory\Transaction;
use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Computes the cross-module, organization-scoped aggregates that power the
 * Executive (Eagle Eye) dashboard.
 */
class ExecutiveOverviewService
{
    /**
     * How long a full dashboard payload is cached before it is recomputed.
     */
    private const CACHE_TTL = 300;

    /**
     * The canonical order widgets are rendered in when a user has no custom
     * layout stored yet.
     *
     * @var array<int, string>
     */
    public const DEFAULT_ORDER = [
        'revenue-overview',
        'stock-allocation',
        'stock-health',
        'cash-position',
        'subscription-health',
        'people-overview',
        'attendance-pulse',
        'activity-feed',
    ];

    /**
     * Build a complete dashboard payload keyed by the organization, cached for
     * a few minutes so repeated visits do not re-run every aggregate query.
     *
     * The active user can always force a fresh payload via `refresh()`.
     *
     * @return array<string, mixed>
     */
    public function build(Organization $organization): array
    {
        return Cache::remember(
            self::cacheKey($organization),
            self::CACHE_TTL,
            fn () => $this->compute($organization)
        );
    }

    /**
     * Drop any cached payload for an organization (e.g. before a data refresh).
     */
    public static function forget(Organization $organization): void
    {
        Cache::forget(self::cacheKey($organization));
    }

    /**
     * The cache key holding an organization's dashboard payload.
     */
    private static function cacheKey(Organization $organization): string
    {
        return 'dashboard:executive:'.(int) $organization->id;
    }

    /**
     * Compute the complete payload from source data.
     *
     * @return array<string, mixed>
     */
    protected function compute(Organization $organization): array
    {
        $revenueSeries = $this->revenueSeries($organization);
        $stockByStore = $this->stockByStore($organization);
        $stockHealth = $this->stockHealth($organization);
        $subscriptions = $this->subscriptionStatus($organization);
        $headcount = $this->headcountByUnit($organization);
        $attendance = $this->attendancePulse($organization);
        $cash = (float) BankAccount::query()
            ->where('organization_id', $organization->id)
            ->sum('current_balance');

        $lowStock = $stockHealth['low'] + $stockHealth['out'];
        $attendanceRate = $headcount['total'] > 0
            ? (int) round(($attendance['today'] * 100) / $headcount['total'])
            : 0;

        return [
            'organization' => [
                'name' => $organization->name,
            ],
            'kpis' => [
                $this->kpi('revenue', 'Net Revenue', $revenueSeries['net'], 'currency',
                    'primary', 'currency-dollar', route('accounting.index'),
                    $this->pctDelta($revenueSeries['current_month'], $revenueSeries['previous_month'])),
                $this->kpi('cash', 'Cash Position', $cash, 'currency',
                    'primary', 'building-library', route('accounting.bank-accounts.index')),
                $this->kpi('inventory', 'Inventory Value', $stockByStore['total_value'], 'currency',
                    'primary', 'cube', route('inventory.index')),
                $this->kpi('active_members', 'Active Members', $subscriptions['active'], 'number',
                    'primary', 'user-group', route('members.index')),
                $this->kpi('headcount', 'Headcount', $headcount['total'], 'number',
                    'primary', 'briefcase', route('hrm.dashboard')),
                $this->kpi('low_stock', 'Low Stock Alerts', $lowStock, 'number',
                    $lowStock > 0 ? 'error' : 'success', 'hand-raised', route('inventory.reports.low-stock')),
                $this->kpi('attendance', 'Attendance Today', $attendanceRate, 'percent',
                    $attendanceRate < 75 ? 'warning' : 'success', 'clock', route('attendance.dashboard')),
            ],
            'widgets' => [
                'revenue-overview' => [
                    'title' => 'Revenue & Expenses',
                    'subtitle' => 'Net performance over the last 12 months',
                    'type' => 'area',
                    'labels' => $revenueSeries['labels'],
                    'series' => [
                        ['name' => 'Revenue', 'data' => $revenueSeries['revenue']],
                        ['name' => 'Expenses', 'data' => $revenueSeries['expense']],
                    ],
                    'currency' => true,
                ],
                'stock-allocation' => [
                    'title' => 'Stock Value by Store',
                    'subtitle' => "Inventory value in {$stockByStore['stores']->count()} stores",
                    'type' => 'bars',
                    'labels' => $stockByStore['stores']->pluck('name')->all(),
                    'values' => $stockByStore['stores']->pluck('value')->all(),
                    'currency' => true,
                ],
                'stock-health' => [
                    'title' => 'Stock Health',
                    'subtitle' => 'Items by stocking level',
                    'type' => 'donut',
                    'data' => [
                        ['label' => 'Healthy', 'value' => $stockHealth['healthy'], 'color' => 'success'],
                        ['label' => 'Low', 'value' => $stockHealth['low'], 'color' => 'warning'],
                        ['label' => 'Out', 'value' => $stockHealth['out'], 'color' => 'error'],
                    ],
                ],
                'cash-position' => [
                    'title' => 'Cash Position',
                    'subtitle' => 'Across active bank accounts',
                    'type' => 'gauge',
                    'value' => $cash,
                    'currency' => true,
                ],
                'subscription-health' => [
                    'title' => 'Subscription Health',
                    'subtitle' => 'Membership renewals at a glance',
                    'type' => 'donut',
                    'data' => [
                        ['label' => 'Active', 'value' => $subscriptions['active'], 'color' => 'success'],
                        ['label' => 'Expiring', 'value' => $subscriptions['expiring'], 'color' => 'warning'],
                        ['label' => 'Expired', 'value' => $subscriptions['expired'], 'color' => 'error'],
                    ],
                ],
                'people-overview' => [
                    'title' => 'Headcount by Unit',
                    'subtitle' => 'Active employees across units',
                    'type' => 'bars',
                    'labels' => $headcount['units']->pluck('unit')->all(),
                    'values' => $headcount['units']->pluck('total')->all(),
                    'currency' => false,
                ],
                'attendance-pulse' => [
                    'title' => 'Attendance Pulse',
                    'subtitle' => 'Daily attendance over the last 14 days',
                    'type' => 'spark',
                    'labels' => $attendance['labels'],
                    'values' => $attendance['values'],
                ],
                'activity-feed' => [
                    'title' => 'Live Operations Feed',
                    'subtitle' => 'Latest activity across the organization',
                    'type' => 'feed',
                ],
            ],
            'activity' => $this->activityFeed($organization),
        ];
    }

    /**
     * Build a single hero KPI descriptor.
     *
     * @return array<string, mixed>
     */
    private function kpi(
        string $key,
        string $label,
        float|int $value,
        string $format,
        string $tone,
        string $icon,
        ?string $href = null,
        ?int $delta = null
    ): array {
        $formatted = match ($format) {
            'currency' => $this->formatMoney((float) $value),
            'percent' => number_format((float) $value, 0).'%',
            default => number_format((int) round((float) $value)),
        };

        return [
            'key' => $key,
            'label' => $label,
            'value' => (float) $value,
            'display' => $formatted,
            'format' => $format,
            'tone' => $tone,
            'icon' => $icon,
            'href' => $href,
            'delta' => $delta,
        ];
    }

    /**
     * Percentage change between a current and previous value (0 when previous
     * is zero or absent).
     */
    private function pctDelta(int|float $current, int|float|null $previous): int
    {
        if ($previous === null || (int) $previous === 0) {
            return 0;
        }

        return (int) round((($current - $previous) * 100) / $previous);
    }

    /**
     * Revenue/expense per month for the last 12 months, bucketed in PHP so the
     * query stays database agnostic.
     *
     * @return array<string, mixed>
     */
    private function revenueSeries(Organization $organization): array
    {
        $months = collect();
        $now = now()->startOfMonth();

        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $months[$date->format('Y-m')] = $date->format('M');
        }

        $start = $now->copy()->subMonths(11);

        $entries = DB::table('ledger_entries as le')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'le.chart_of_account_id')
            ->where('le.organization_id', $organization->id)
            ->where('le.entry_date', '>=', $start->format('Y-m-d'))
            ->whereIn('coa.type', ['revenue', 'expense'])
            ->selectRaw('le.entry_date, le.type, le.amount, coa.type as account_type')
            ->get();

        $revenue = array_fill_keys($months->keys()->all(), 0.0);
        $expense = array_fill_keys($months->keys()->all(), 0.0);

        foreach ($entries as $entry) {
            $entryDate = Carbon::parse($entry->entry_date);
            $key = $entryDate->format('Y-m');

            if (! array_key_exists($key, $revenue)) {
                continue;
            }

            $flow = $entry->type === 'credit' ? (float) $entry->amount : -((float) $entry->amount);

            if ($entry->account_type === 'revenue') {
                $revenue[$key] += $flow;
            } else {
                $expense[$key] -= $flow;
            }
        }

        foreach ($revenue as $key => $value) {
            $revenue[$key] = round(max(0, $value), 2);
            $expense[$key] = round(max(0, $expense[$key]), 2);
        }

        $revenueValues = array_values($revenue);
        $expenseValues = array_values($expense);
        $netValues = array_map(fn ($r, $e) => max(0, $r - $e), $revenueValues, $expenseValues);

        // Most recent month and the month before it (net), for the KPI delta.
        $keyCount = count($netValues);
        $currentMonthNet = $keyCount > 0 ? $netValues[$keyCount - 1] : 0;
        $previousMonthNet = $keyCount > 1 ? $netValues[$keyCount - 2] : null;

        return [
            'labels' => $months->values()->all(),
            'revenue' => $revenueValues,
            'expense' => $expenseValues,
            'net' => round(array_sum($revenue) - array_sum($expense), 2),
            'current_month' => $currentMonthNet,
            'previous_month' => $previousMonthNet,
        ];
    }

    /**
     * Inventory value grouped by store for the organization.
     *
     * @return array<string, mixed>
     */
    private function stockByStore(Organization $organization): array
    {
        $stores = DB::table('inventory_stores as s')
            ->join('organization_units as ou', 'ou.id', '=', 's.organization_unit_id')
            ->join('inventory_store_items as si', 'si.store_id', '=', 's.id')
            ->join('inventory_items as i', 'i.id', '=', 'si.item_id')
            ->where('ou.organization_id', $organization->id)
            ->whereNull('s.deleted_at')
            ->whereNull('i.deleted_at')
            ->selectRaw('s.id, s.name, SUM(si.quantity * i.cost_price) as value, COUNT(DISTINCT si.item_id) as item_count')
            ->groupBy('s.id', 's.name')
            ->orderByDesc('value')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'value' => (float) $row->value,
                'item_count' => (int) $row->item_count,
            ]);

        return [
            'stores' => $stores,
            'total_value' => (float) $stores->sum('value'),
        ];
    }

    /**
     * Bucket every item into healthy / low / out based on total quantity versus
     * its reorder level.
     *
     * @return array<string, int>
     */
    private function stockHealth(Organization $organization): array
    {
        $rows = DB::table('inventory_store_items as si')
            ->join('inventory_items as i', 'i.id', '=', 'si.item_id')
            ->where('i.organization_id', $organization->id)
            ->whereNull('i.deleted_at')
            ->selectRaw('i.id, i.reorder_level, SUM(si.quantity) as total_qty')
            ->groupBy('i.id', 'i.reorder_level')
            ->get();

        $health = ['healthy' => 0, 'low' => 0, 'out' => 0];

        foreach ($rows as $row) {
            $qty = (int) $row->total_qty;
            $reorder = (int) $row->reorder_level;

            if ($qty <= 0) {
                $health['out']++;
            } elseif ($reorder > 0 && $qty <= $reorder) {
                $health['low']++;
            } else {
                $health['healthy']++;
            }
        }

        return $health;
    }

    /**
     * Active / expiring / expired membership subscription buckets.
     *
     * Resolves all three counts in a single query using conditional SUM to
     * avoid three separate passes over the same table.
     *
     * The expired bucket excludes 'cancelled' subscriptions so that a member
     * who intentionally cancelled is not surfaced as an error-level alert.
     *
     * @return array<string, int>
     */
    private function subscriptionStatus(Organization $organization): array
    {
        $now = now();

        $row = MemberSubscription::query()
            ->where('organization_id', $organization->id)
            ->selectRaw('
                SUM(CASE WHEN status = ? AND start_date <= ? AND end_date >= ? THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = ? AND end_date <= ? AND end_date > ? THEN 1 ELSE 0 END) as expiring,
                SUM(CASE WHEN end_date < ? AND status != ? THEN 1 ELSE 0 END) as expired
            ', [
                'active', $now->toDateString(), $now->toDateString(),
                'active', $now->copy()->addDays(30)->toDateString(), $now->toDateString(),
                $now->toDateString(), 'cancelled',
            ])
            ->first();

        return [
            'active' => (int) ($row->active ?? 0),
            'expiring' => (int) ($row->expiring ?? 0),
            'expired' => (int) ($row->expired ?? 0),
        ];
    }

    /**
     * Active employee headcount grouped by organizational unit.
     *
     * The unit name is coalesced in PHP so that two units which share the same
     * name are not merged into one bucket, and a real unit named "Unassigned"
     * cannot collide with the fallback bucket.
     *
     * @return array<string, mixed>
     */
    private function headcountByUnit(Organization $organization): array
    {
        $units = DB::table('employees as e')
            ->leftJoin('organization_units as ou', 'ou.id', '=', 'e.organization_unit_id')
            ->where('e.organization_id', $organization->id)
            ->where('e.is_active', true)
            ->whereNull('e.deleted_at')
            ->selectRaw('ou.id as unit_id, ou.name as unit_name, COUNT(*) as total')
            ->groupBy('ou.id', 'ou.name')
            ->get()
            ->map(fn ($row) => [
                'unit' => $row->unit_name ?? 'Unassigned',
                'total' => (int) $row->total,
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'units' => $units,
            'total' => (int) $units->sum('total'),
        ];
    }

    /**
     * Today's attendance plus a daily-present sparkline for the last 14 days.
     *
     * Only records where the employee actually attended (present or late) are
     * counted, so leave / absent / missed-punch records do not inflate the
     * rate; the denominator stays the active headcount.
     *
     * @return array<string, mixed>
     */
    private function attendancePulse(Organization $organization): array
    {
        $labels = collect();
        $start = today()->subDays(13);

        for ($i = 0; $i < 14; $i++) {
            $date = $start->copy()->addDays($i);
            $labels[$date->format('Y-m-d')] = $date->format('D');
        }

        $records = AttendanceRecord::query()
            ->where('organization_id', $organization->id)
            ->where('record_date', '>=', $start->format('Y-m-d'))
            ->whereIn('status', ['present', 'late'])
            ->selectRaw('record_date, COUNT(*) as total')
            ->groupBy('record_date')
            ->get()
            ->keyBy(fn ($record) => Carbon::parse($record->record_date)->format('Y-m-d'))
            ->map(fn ($record) => (int) $record->total);

        return [
            'labels' => $labels->values()->all(),
            'values' => $labels->keys()->map(fn ($day) => (int) ($records[$day] ?? 0))->all(),
            'today' => (int) ($records[today()->format('Y-m-d')] ?? 0),
        ];
    }

    /**
     * A unified, time-sorted feed of the latest organizational activity.
     *
     * @return array<int, array<string, mixed>>
     */
    private function activityFeed(Organization $organization): array
    {
        $items = collect();

        Transaction::query()
            ->whereHas('store.organization_unit', fn ($query) => $query->where('organization_id', $organization->id))
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'reference', 'status', 'created_at'])
            ->each(function ($transaction) use ($items) {
                $items->push([
                    'type' => 'transaction',
                    'title' => $transaction->reference ?: 'Stock transaction',
                    'meta' => ucfirst($transaction->status ?? 'Pending'),
                    'href' => route('inventory.transactions.show', $transaction->id),
                    'time' => $transaction->created_at,
                ]);
            });

        Voucher::query()
            ->where('organization_id', $organization->id)
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'type', 'number', 'amount', 'created_at'])
            ->each(function ($voucher) use ($items) {
                $items->push([
                    'type' => 'voucher',
                    'title' => 'Voucher '.($voucher->number ?: '#'.$voucher->id),
                    'meta' => ucfirst($voucher->type ?? 'General'),
                    'href' => route('accounting.index'),
                    'time' => $voucher->created_at,
                ]);
            });

        Member::query()
            ->where('organization_id', $organization->id)
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'first_name', 'last_name', 'created_at'])
            ->each(function ($member) use ($items) {
                $items->push([
                    'type' => 'member',
                    'title' => trim($member->first_name.' '.$member->last_name) ?: 'New member',
                    'meta' => 'New member onboarded',
                    'href' => route('members.show', $member->id),
                    'time' => $member->created_at,
                ]);
            });

        MemberSubscription::query()
            ->with('member:id,first_name,last_name')
            ->where('organization_id', $organization->id)
            ->latest('created_at')
            ->limit(5)
            ->get(['id', 'member_id', 'status', 'created_at'])
            ->each(function ($subscription) use ($items) {
                $items->push([
                    'type' => 'subscription',
                    'title' => trim(($subscription->member?->first_name ?? '').' '.($subscription->member?->last_name ?? '')) ?: 'Subscription',
                    'meta' => 'Subscription '.ucfirst($subscription->status ?? 'created'),
                    'href' => route('subscriptions.show', $subscription->id),
                    'time' => $subscription->created_at,
                ]);
            });

        return $items
            ->sortByDesc(fn ($item) => $item['time']->timestamp)
            ->take(12)
            ->values()
            ->map(fn ($item) => [...$item, 'time' => $item['time']->diffForHumans()])
            ->all();
    }

    /**
     * Compact, locale-agnostic currency formatting.
     */
    private function formatMoney(float $value): string
    {
        return '$'.number_format($value, 2);
    }
}
