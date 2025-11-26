<?php

namespace App\Livewire\Accounting;

use App\Models\Vendor;
use App\Services\OutstandingStatementsService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class PayablesOutstanding extends Component
{
    use WithPagination;

    public ?int $vendorId = null;

    public string $startDate = '';

    public string $endDate = '';

    public string $asOfDate = '';

    public string $search = '';

    public int $perPage = 10;

    public bool $showDetails = false;

    public array $selectedVendor = [];

    protected OutstandingStatementsService $service;

    protected $queryString = [
        'vendorId',
        'startDate',
        'endDate',
        'asOfDate',
        'search',
        'perPage',
    ];

    public function boot(OutstandingStatementsService $service): void
    {
        $this->service = $service;
    }

    public function mount(): void
    {
        $this->asOfDate = now()->format('Y-m-d');
        $this->startDate = now()->subMonths(3)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    #[Computed]
    public function statement(): array
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        $asOfDate = $this->asOfDate ? Carbon::parse($this->asOfDate) : null;

        return $this->service->generatePayablesStatement(
            vendorId: $this->vendorId,
            asOfDate: $asOfDate,
            startDate: $startDate,
            endDate: $endDate
        );
    }

    #[Computed]
    public function vendors(): \Illuminate\Database\Eloquent\Collection
    {
        return Vendor::where('organization_id', auth()->user()->current_organization_id)
            ->when($this->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->get();
    }

    public function updated($property): void
    {
        if (in_array($property, ['vendorId', 'startDate', 'endDate', 'asOfDate'])) {
            $this->resetPage();
        }
    }

    public function refreshStatement(): void
    {
        $this->resetPage();
    }

    public function showVendorDetails(array $vendorStatement): void
    {
        $this->selectedVendor = $vendorStatement;
        $this->showDetails = true;
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->selectedVendor = [];
    }

    public function exportPdf(): void
    {
        $params = [
            'vendor_id' => $this->vendorId,
            'as_of_date' => $this->asOfDate,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ];

        $url = route('accounting.download.payables-outstanding').'?'.http_build_query($params);
        $this->redirect($url);
    }

    public function exportExcel(): void
    {
        // For now, redirect to PDF export
        // Excel export can be implemented later with Laravel Excel
        $this->exportPdf();
    }

    public function resetFilters(): void
    {
        $this->reset(['vendorId', 'search', 'startDate', 'endDate']);
        $this->asOfDate = now()->format('Y-m-d');
        $this->startDate = now()->subMonths(3)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->resetPage();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.accounting.payables-outstanding');
    }
}
