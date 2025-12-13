<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Services\Membership\MembershipService;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AdvancedMemberList extends Component
{
    use WithPagination;

    // Search and Filters
    public string $search = '';

    public string $status = 'all';

    public string $subscriptionStatus = 'all';

    public string $dateRange = 'all';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 15;

    public bool $hasFamily = false;

    public string $joinDateFrom = '';

    public string $joinDateTo = '';

    public string $expiryDateFrom = '';

    public string $expiryDateTo = '';

    // Bulk Operations
    public array $selectedMembers = [];

    public string $bulkAction = '';

    public bool $selectAll = false;

    // Export
    public string $exportFormat = 'csv';

    public array $exportColumns = [
        'membership_number' => true,
        'full_name' => true,
        'email' => true,
        'phone' => true,
        'status' => true,
        'join_date' => true,
        'expiry_date' => true,
        'family_count' => true,
    ];

    // Filter Presets
    public array $filterPresets = [];

    public string $presetName = '';

    // Export data
    public string $csvContent = '';

    public string $csvFilename = '';

    // UI State
    public bool $showFilters = false;

    public bool $showExportModal = false;

    public bool $showBulkActions = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'subscriptionStatus' => ['except' => 'all'],
        'dateRange' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
        'hasFamily' => ['except' => false],
    ];

    public array $statuses = [
        'all' => 'All Members',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
    ];

    public array $subscriptionStatuses = [
        'all' => 'All Subscriptions',
        'active' => 'Active',
        'expired' => 'Expired',
        'cancelled' => 'Cancelled',
        'pending' => 'Pending',
    ];

    public array $dateRanges = [
        'all' => 'All Time',
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'this_week' => 'This Week',
        'last_week' => 'Last Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'last_30_days' => 'Last 30 Days',
        'last_90_days' => 'Last 90 Days',
        'this_year' => 'This Year',
        'last_year' => 'Last Year',
        'custom' => 'Custom Range',
    ];

    public array $sortOptions = [
        'created_at' => 'Date Created',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'membership_number' => 'Membership Number',
        'join_date' => 'Join Date',
        'expiry_date' => 'Expiry Date',
        'email' => 'Email',
    ];

    public array $bulkActions = [
        'activate' => 'Activate Members',
        'deactivate' => 'Deactivate Members',
        'suspend' => 'Suspend Members',
        'reactivate' => 'Reactivate Members',
        'send_email' => 'Send Email',
        'send_sms' => 'Send SMS',
        'export_selected' => 'Export Selected',
    ];

    public function mount(): void
    {
        $this->authorize('membership.view_members');
        $this->loadFilterPresets();
    }

    public function render(MembershipService $membershipService)
    {
        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        if (! $organizationId) {
            throw new \Exception('No organization found for user');
        }

        try {
            $members = $this->getMembersQuery($organizationId)
                ->with(['familyMembers', 'activeSubscription'])
                ->paginate($this->perPage);

            $statistics = $membershipService->getMemberStatistics($organizationId);

            return view('livewire.membership.advanced-member-list', [
                'members' => $members,
                'statistics' => $statistics,
            ]);
        } catch (\Exception $e) {
            return view('livewire.membership.advanced-member-list', [
                'members' => collect(),
                'statistics' => ['total_members' => 0, 'active_members' => 0],
            ]);
        }
    }

    protected function getMembersQuery(int $organizationId)
    {
        $query = Member::where('organization_id', $organizationId);

        // Apply search
        if (! empty($this->search)) {
            $query->search($this->search);
        }

        // Apply status filter
        if ($this->status !== 'all') {
            $query->byStatus($this->status);
        }

        // Apply subscription status filter
        if ($this->subscriptionStatus !== 'all') {
            $query->whereHas('subscriptions', function ($q) {
                $q->where('status', $this->subscriptionStatus);
            });
        }

        // Apply date range filter
        if ($this->dateRange !== 'all') {
            $dateFilter = $this->getDateFilter($this->dateRange);
            if ($dateFilter) {
                $query->whereBetween('join_date', $dateFilter);
            }
        }

        // Apply custom date range
        if ($this->dateRange === 'custom') {
            if ($this->joinDateFrom) {
                $query->whereDate('join_date', '>=', $this->joinDateFrom);
            }
            if ($this->joinDateTo) {
                $query->whereDate('join_date', '<=', $this->joinDateTo);
            }
            if ($this->expiryDateFrom) {
                $query->whereDate('expiry_date', '>=', $this->expiryDateFrom);
            }
            if ($this->expiryDateTo) {
                $query->whereDate('expiry_date', '<=', $this->expiryDateTo);
            }
        }

        // Apply family filter
        if ($this->hasFamily) {
            $query->has('familyMembers');
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query;
    }

    protected function getDateFilter(string $range): ?array
    {
        return match ($range) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'last_30_days' => [now()->subDays(30), now()],
            'last_90_days' => [now()->subDays(90), now()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'last_year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            default => null,
        };
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSubscriptionStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $user = auth()->user();
            $organizationId = $user->current_organization_id ??
                             $user->operating_organization_id ??
                             $user->organizations()->first()?->id;

            $this->selectedMembers = $this->getMembersQuery($organizationId)
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedMembers = [];
        }
    }

    public function performBulkAction(): void
    {
        $this->authorize('membership.manage_members');

        if (empty($this->selectedMembers) || empty($this->bulkAction)) {
            return;
        }

        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        $members = Member::where('organization_id', $organizationId)
            ->whereIn('id', $this->selectedMembers)
            ->get();

        DB::transaction(function () use ($members) {
            foreach ($members as $member) {
                match ($this->bulkAction) {
                    'activate' => $member->update(['status' => 'active']),
                    'deactivate' => $member->update(['status' => 'inactive']),
                    'suspend' => app(MembershipService::class)->suspendMember($member),
                    'reactivate' => app(MembershipService::class)->reactivateMember($member),
                    'export_selected' => $this->exportSelectedMembers($members),
                    default => null,
                };
            }
        });

        $this->dispatch('bulk-action-completed', action: $this->bulkAction, count: $members->count());
        $this->reset(['selectedMembers', 'selectAll', 'bulkAction']);
        $this->resetPage();
    }

    public function exportMembers(string $format): void
    {
        $this->authorize('membership.export_data');

        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        $members = $this->getMembersQuery($organizationId)
            ->with(['familyMembers'])
            ->get();

        $filename = 'members_export_'.now()->format('Y-m-d_H-i-s').".{$format}";

        if ($format === 'csv') {
            $this->exportToCsv($members, $filename);
        } elseif ($format === 'pdf') {
            $this->exportToPdf($members, $filename);
        }

        // Always dispatch the completion event
        $this->dispatch('export-completed', filename: $filename);
    }

    protected function exportToCsv($members, string $filename): void
    {
        // For testing and compatibility, we'll store the CSV content
        $csvContent = '';
        
        // Header row
        $headers = [];
        foreach ($this->exportColumns as $column => $enabled) {
            if ($enabled) {
                $headers[] = ucwords(str_replace('_', ' ', $column));
            }
        }
        $csvContent .= implode(',', $headers) . "\n";

        // Data rows
        foreach ($members as $member) {
            $row = [];
            foreach ($this->exportColumns as $column => $enabled) {
                if ($enabled) {
                    $value = match ($column) {
                        'full_name' => $member->full_name,
                        'family_count' => $member->familyMembers->count(),
                        'join_date' => $member->join_date?->format('Y-m-d'),
                        'expiry_date' => $member->expiry_date?->format('Y-m-d'),
                        default => $member->$column,
                    };
                    $row[] = '"' . str_replace('"', '""', (string) $value) . '"';
                }
            }
            $csvContent .= implode(',', $row) . "\n";
        }

        // Store the CSV content for download
        $this->csvContent = $csvContent;
        $this->csvFilename = $filename;
        
        // In production, this would trigger a file download
        // For now, we'll just dispatch the event to indicate completion
        $this->dispatch('export-completed', filename: $filename);
    }

    protected function exportToPdf($members, string $filename): void
    {
        // PDF export implementation would go here
        // For now, we'll just dispatch an event
        $this->dispatch('pdf-export-initiated', count: $members->count());
    }

    protected function exportSelectedMembers($members): void
    {
        $filename = 'selected_members_export_'.now()->format('Y-m-d_H-i-s').'.csv';
        $this->exportToCsv($members, $filename);
    }

    public function saveFilterPreset(string $name = null): void
    {
        $presetName = $name ?? $this->presetName;
        
        if (empty($presetName)) {
            return;
        }

        $preset = [
            'name' => $presetName,
            'filters' => [
                'status' => $this->status,
                'subscriptionStatus' => $this->subscriptionStatus,
                'dateRange' => $this->dateRange,
                'sortBy' => $this->sortBy,
                'sortDirection' => $this->sortDirection,
                'perPage' => $this->perPage,
                'hasFamily' => $this->hasFamily,
            ],
        ];

        // Save to user preferences or database
        $this->filterPresets[$presetName] = $preset;

        $this->dispatch('filter-preset-saved', name: $presetName);
        $this->reset('presetName');
    }

    public function loadFilterPreset(string $presetName): void
    {
        if (! isset($this->filterPresets[$presetName])) {
            return;
        }

        $preset = $this->filterPresets[$presetName]['filters'];

        foreach ($preset as $key => $value) {
            $this->$key = $value;
        }

        $this->resetPage();
        $this->dispatch('filter-preset-loaded', name: $presetName);
    }

    public function deleteFilterPreset(string $presetName): void
    {
        unset($this->filterPresets[$presetName]);
        $this->dispatch('filter-preset-deleted', name: $presetName);
    }

    protected function loadFilterPresets(): void
    {
        // Load presets from user preferences or database
        // For now, we'll use some default presets
        $this->filterPresets = [
            'Active Members' => [
                'name' => 'Active Members',
                'filters' => [
                    'status' => 'active',
                    'subscriptionStatus' => 'all',
                    'dateRange' => 'all',
                    'sortBy' => 'created_at',
                    'sortDirection' => 'desc',
                    'perPage' => 15,
                    'hasFamily' => false,
                ],
            ],
            'Expiring Soon' => [
                'name' => 'Expiring Soon',
                'filters' => [
                    'status' => 'active',
                    'subscriptionStatus' => 'all',
                    'dateRange' => 'all',
                    'sortBy' => 'expiry_date',
                    'sortDirection' => 'asc',
                    'perPage' => 15,
                    'hasFamily' => false,
                ],
            ],
        ];
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'status',
            'subscriptionStatus',
            'dateRange',
            'sortBy',
            'sortDirection',
            'perPage',
            'hasFamily',
            'joinDateFrom',
            'joinDateTo',
            'expiryDateFrom',
            'expiryDateTo',
        ]);

        $this->resetPage();
    }

    #[On('member-created')]
    #[On('member-updated')]
    #[On('member-deleted')]
    public function refreshMembers(): void
    {
        $this->resetPage();
    }

    public function getExpiringMembersProperty(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        return app(MembershipService::class)->getExpiringMembers($organizationId, 30);
    }

    public function viewMember(int $memberId): void
    {
        $this->dispatch('open-member-details', memberId: $memberId);
    }

    public function editMember(int $memberId): void
    {
        $this->dispatch('open-member-form', memberId: $memberId);
    }

    public function deleteMember(int $memberId): void
    {
        $this->authorize('membership.delete_members');

        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        $member = Member::where('organization_id', $organizationId)
            ->findOrFail($memberId);

        $member->delete();

        $this->dispatch('member-deleted', memberId: $memberId);
        $this->resetPage();
    }

    public function previousPage(): void
    {
        $this->setPage(max($this->getPage() - 1, 1));
    }

    public function nextPage(): void
    {
        $this->setPage($this->getPage() + 1);
    }
}
