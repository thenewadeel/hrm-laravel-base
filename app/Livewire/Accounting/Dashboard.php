<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\LedgerEntry;
use App\Models\Scopes\OrganizationScope;
use App\Services\AccountingReportService;
use Livewire\Component;

class Dashboard extends Component
{
    protected AccountingReportService $reportService;

    public $summary;

    public array $recentActivity = [];

    public function mount(AccountingReportService $reportService)
    {
        $this->reportService = $reportService;
        $this->generateSummary();
        $this->loadRecentActivity();
    }

    public function generateSummary()
    {
        $this->summary = $this->reportService->getDashboardSummary();
    }

    public function loadRecentActivity()
    {
        $organizationId = auth()->user()->current_organization_id ??
            auth()->user()->operating_organization_id ??
            null;

        $query = LedgerEntry::query()
            ->with(['account'])
            ->withoutGlobalScope(OrganizationScope::class);

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        $entries = $query->latest('entry_date')
            ->latest('id')
            ->limit(10)
            ->get();

        $this->recentActivity = $entries->map(function (LedgerEntry $entry) {
            $isCredit = $entry->type === 'credit';

            return [
                'description' => $entry->description,
                'account' => $entry->account?->name ?? 'General Ledger',
                'date' => $entry->entry_date->format('M d, Y'),
                'amount' => (float) $entry->amount,
                'type' => $entry->type,
                'signed_amount' => $isCredit ? -1 * (float) $entry->amount : (float) $entry->amount,
                'entry_date' => $entry->entry_date,
            ];
        })->sortByDesc('entry_date')->values()->toArray();
    }

    public function render()
    {
        return view('livewire.accounting.dashboard');
    }
}
