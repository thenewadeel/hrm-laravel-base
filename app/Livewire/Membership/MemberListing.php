<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class MemberListing extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $sortBy = 'first_name';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    public array $selectedMembers = [];

    public string $bulkAction = '';

    public array $memberStats = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortBy' => ['except' => 'first_name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 15],
    ];

    public function mount(): void
    {
        try {
            $this->authorize('membership.view_members');
            $this->loadStatistics();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        } catch (\Exception $e) {
            // Handle other exceptions gracefully
        }
    }

    public function render()
    {
        try {
            $organizationId = Auth::user()->current_organization_id;
        } catch (\Exception $e) {
            $organizationId = null;
        }

        $members = $this->getMembers($organizationId);

        return view('livewire.membership.member-listing', [
            'members' => $members,
            'memberStats' => $this->memberStats,
            'canExportMembers' => $this->canExportMembers,
            'canManageMembers' => $this->canManageMembers,
        ]);
    }

    public function getMembers(?int $organizationId)
    {
        if (! $organizationId) {
            return Member::query()->whereRaw('1 = 0')->paginate(15);
        }

        $query = Member::where('organization_id', $organizationId);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('membership_number', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting
        if (in_array($this->sortBy, ['first_name', 'last_name', 'email', 'membership_number', 'status', 'created_at'])) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        }

        return $query->paginate($this->perPage);
    }

    public function loadStatistics(): void
    {
        try {
            $organizationId = Auth::user()->current_organization_id;

            if (! $organizationId) {
                $this->memberStats = [
                    'total' => 0,
                    'active' => 0,
                    'inactive' => 0,
                    'expired' => 0,
                ];

                return;
            }

            $query = Member::where('organization_id', $organizationId);

            $this->memberStats = [
                'total' => $query->count(),
                'active' => $query->clone()->where('status', 'active')->count(),
                'inactive' => $query->clone()->where('status', 'inactive')->count(),
                'expired' => $query->clone()->where('status', 'expired')->count(),
            ];
        } catch (\Exception $e) {
            $this->memberStats = [
                'total' => 0,
                'active' => 0,
                'inactive' => 0,
                'expired' => 0,
            ];
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function exportMembers(): void
    {
        try {
            $this->authorize('membership.export_data');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        }

        try {
            $organizationId = Auth::user()->current_organization_id;

            if (! $organizationId) {
                $this->dispatch('export-error', message: 'No organization selected');

                return;
            }

            $query = Member::where('organization_id', $organizationId);
            $members = $query->get();

            $csv = "ID,Membership Number,First Name,Last Name,Email,Phone,Status,Join Date,Expiry Date\n";

            foreach ($members as $member) {
                $csv .= sprintf(
                    "%s,%s,%s,%s,%s,%s,%s,%s,%s\n",
                    $member->id,
                    $member->membership_number,
                    $member->first_name,
                    $member->last_name,
                    $member->email,
                    $member->phone,
                    ucfirst($member->status),
                    $member->created_at->format('Y-m-d'),
                    $member->expiry_date ? $member->expiry_date->format('Y-m-d') : '',
                );
            }

            $filename = 'members_export_'.now()->format('Y_m_d_H_i_s').'.csv';

            $this->dispatch('export-completed', [
                'filename' => $filename,
                'data' => $csv,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('export-error', message: 'Export failed: '.$e->getMessage());
        }
    }

    public function bulkUpdateStatus(string $status): void
    {
        try {
            $this->authorize('membership.manage_members');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403);
        }

        try {
            $organizationId = Auth::user()->current_organization_id;

            if (! $organizationId || empty($this->selectedMembers)) {
                $this->dispatch('bulk-update-error', message: 'No members selected');

                return;
            }

            $updated = Member::where('organization_id', $organizationId)
                ->whereIn('id', $this->selectedMembers)
                ->update(['status' => $status]);

            $this->selectedMembers = [];
            $this->loadStatistics();

            $this->dispatch('bulk-update-completed', [
                'message' => "Successfully updated {$updated} member(s)",
                'count' => $updated,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('bulk-update-error', message: 'Bulk update failed: '.$e->getMessage());
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getMemberStatusClass(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            'expired' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'suspended' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    public function getMemberStatusText(string $status): string
    {
        return match ($status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'expired' => 'Expired',
            'suspended' => 'Suspended',
            default => ucfirst($status),
        };
    }

    public function getCanManageMembersProperty(): bool
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return false;
            }

            return $user->can('membership.manage_members');
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCanExportMembersProperty(): bool
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return false;
            }

            return $user->can('membership.export_data');
        } catch (\Exception $e) {
            return false;
        }
    }
}
