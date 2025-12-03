<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Services\Membership\MembershipService;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MemberList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'all';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    public array $statuses = [
        'all' => 'All Members',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
    ];

    public array $sortOptions = [
        'created_at' => 'Date Created',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'membership_number' => 'Membership Number',
        'join_date' => 'Join Date',
        'expiry_date' => 'Expiry Date',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 15],
    ];

    public function mount(): void
    {
        $this->authorize('membership.view_members');
    }

    public function render(MembershipService $membershipService)
    {
        $members = $membershipService->searchMembers(
            organizationId: auth()->user()->current_organization_id,
            search: $this->search,
            filters: [
                'status' => $this->status !== 'all' ? $this->status : null,
                'sort_by' => $this->sortBy,
                'sort_direction' => $this->sortDirection,
                'per_page' => $this->perPage,
            ]
        );

        return view('livewire.membership.member-list', [
            'members' => $members,
            'statistics' => $membershipService->getMemberStatistics(auth()->user()->current_organization_id),
        ]);
    }

    public function deleteMember(int $memberId): void
    {
        $this->authorize('membership.delete_members');

        $member = Member::findOrFail($memberId);
        
        if ($member->organization_id !== auth()->user()->current_organization_id) {
            abort(403);
        }

        $member->delete();

        $this->dispatch('member-deleted', memberId: $memberId);
        $this->dispatch('show-notification', message: 'Member deleted successfully', type: 'success');
    }

    public function printMemberCard(int $memberId): void
    {
        $this->authorize('membership.print_cards');

        $member = Member::findOrFail($memberId);
        
        if ($member->organization_id !== auth()->user()->current_organization_id) {
            abort(403);
        }

        $this->dispatch('print-member-card', memberId: $memberId);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
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

    #[On('member-created')]
    #[On('member-updated')]
    #[On('member-deactivated')]
    #[On('member-reactivated')]
    public function refreshMembers(): void
    {
        $this->resetPage();
    }

    public function getExpiringMembersProperty(): \Illuminate\Support\Collection
    {
        return app(MembershipService::class)->getExpiringMembers(
            auth()->user()->current_organization_id,
            30 // 30 days
        );
    }
}
