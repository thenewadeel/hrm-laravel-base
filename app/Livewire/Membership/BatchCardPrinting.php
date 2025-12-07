<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use App\Services\Membership\CardPrintingService;
use Livewire\Component;
use Livewire\WithPagination;

class BatchCardPrinting extends Component
{
    use WithPagination;

    public array $selectedMembers = [];
    public array $selectedFamilyMembers = [];
    public string $cardTemplate = 'default';
    public string $search = '';
    public string $status = 'all';
    public string $memberType = 'all'; // all, members, family_members
    public bool $selectAll = false;
    public bool $includeFamilyMembers = false;

    public array $availableTemplates = [
        'default' => 'Default Card',
        'premium' => 'Premium Card',
        'family' => 'Family Card',
        'corporate' => 'Corporate Card',
    ];

    public array $statuses = [
        'all' => 'All Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
        'expired' => 'Expired',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'memberType' => ['except' => 'all'],
        'cardTemplate' => ['except' => 'default'],
    ];

    public function mount(): void
    {
        $this->authorize('membership.print_cards');
    }

    public function render(CardPrintingService $cardService)
    {
        $organizationId = auth()->user()->current_organization_id;

        $membersQuery = Member::where('organization_id', $organizationId);
        $familyMembersQuery = FamilyMember::where('organization_id', $organizationId);

        // Apply search
        if ($this->search) {
            $membersQuery->where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('membership_number', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            });

            $familyMembersQuery->where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode_number', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->status !== 'all') {
            $membersQuery->where('status', $this->status);
            $familyMembersQuery->where('status', $this->status);
        }

        $members = $membersQuery->with('family_members')->paginate(25);
        $familyMembers = $familyMembersQuery->with('primary_member')->paginate(25);

        return view('livewire.membership.batch-card-printing', [
            'members' => $members,
            'familyMembers' => $familyMembers,
            'cardStatistics' => $cardService->getCardStatistics($organizationId),
        ]);
    }

    public function updatedSelectAll(): void
    {
        if ($this->selectAll) {
            $organizationId = auth()->user()->current_organization_id;
            
            $membersQuery = Member::where('organization_id', $organizationId);
            $familyMembersQuery = FamilyMember::where('organization_id', $organizationId);

            // Apply filters
            if ($this->search) {
                $membersQuery->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%')
                          ->orWhere('membership_number', 'like', '%' . $this->search . '%');
                });

                $familyMembersQuery->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->status !== 'all') {
                $membersQuery->where('status', $this->status);
                $familyMembersQuery->where('status', $this->status);
            }

            $this->selectedMembers = $membersQuery->pluck('id')->toArray();
            $this->selectedFamilyMembers = $familyMembersQuery->pluck('id')->toArray();
        } else {
            $this->selectedMembers = [];
            $this->selectedFamilyMembers = [];
        }
    }

    public function generateBatchCards(CardPrintingService $cardService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;
            
            if (empty($this->selectedMembers) && empty($this->selectedFamilyMembers)) {
                throw new \Exception('Please select at least one member or family member');
            }

            $filters = [
                'member_ids' => $this->selectedMembers,
                'family_member_ids' => $this->selectedFamilyMembers,
                'template' => $this->cardTemplate,
            ];

            $pdfPath = $cardService->bulkGenerateCards($organizationId, $filters);

            $this->dispatch('batch-cards-generated', path: $pdfPath);
            $this->dispatch('show-notification', 
                message: 'Batch cards generated successfully', 
                type: 'success'
            );

            // Clear selections after successful generation
            $this->selectedMembers = [];
            $this->selectedFamilyMembers = [];
            $this->selectAll = false;

        } catch (\Exception $e) {
            $this->dispatch('show-notification', 
                message: 'Error generating batch cards: ' . $e->getMessage(), 
                type: 'error'
            );
        }
    }

    public function downloadBatchCards(CardPrintingService $cardService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;
            
            if (empty($this->selectedMembers) && empty($this->selectedFamilyMembers)) {
                throw new \Exception('Please select at least one member or family member');
            }

            $filters = [
                'member_ids' => $this->selectedMembers,
                'family_member_ids' => $this->selectedFamilyMembers,
                'template' => $this->cardTemplate,
            ];

            $pdfPath = $cardService->bulkGenerateCards($organizationId, $filters);
            $filename = "batch-cards-" . date('Y-m-d-H-i-s') . ".pdf";

            $this->dispatch('download-batch-cards', path: $pdfPath, filename: $filename);

        } catch (\Exception $e) {
            $this->dispatch('show-notification', 
                message: 'Error downloading batch cards: ' . $e->getMessage(), 
                type: 'error'
            );
        }
    }

    public function clearSelection(): void
    {
        $this->selectedMembers = [];
        $this->selectedFamilyMembers = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedMemberType(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function getTotalSelectedProperty(): int
    {
        return count($this->selectedMembers) + count($this->selectedFamilyMembers);
    }

    public function getSelectedMembersCountProperty(): int
    {
        return count($this->selectedMembers);
    }

    public function getSelectedFamilyMembersCountProperty(): int
    {
        return count($this->selectedFamilyMembers);
    }

    public function getCanGenerateBatchProperty(): bool
    {
        return $this->getTotalSelectedProperty() > 0;
    }
}
