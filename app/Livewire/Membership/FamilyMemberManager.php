<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Services\Membership\MembershipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

// QR Code and Barcode generation - using simple implementations for testing

class FamilyMemberManager extends Component
{
    use WithFileUploads, WithPagination;

    public int $memberId;

    public Member $member;

    // Form State
    public bool $showForm = false;

    public ?int $editingFamilyMember = null;

    public array $form = [
        'relationship' => '',
        'title' => '',
        'first_name' => '',
        'last_name' => '',
        'date_of_birth' => '',
        'gender' => '',
        'notes' => '',
    ];

    // Search and Filter
    public string $search = '';

    public string $relationshipFilter = 'all';

    public string $sortBy = 'created_at';

    public string $sortDirection = 'desc';

    public int $perPage = 10;

    // Photo Upload
    public $photo;

    // UI State
    public bool $showQRCode = false;

    public bool $showBarcode = false;

    public string $qrCodeData = '';

    public string $barcodeData = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'relationshipFilter' => ['except' => 'all'],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'perPage' => ['except' => 10],
    ];

    protected $rules = [
        'form.relationship' => 'required|string|max:50',
        'form.title' => 'nullable|string|max:10',
        'form.first_name' => 'required|string|max:100',
        'form.last_name' => 'required|string|max:100',
        'form.date_of_birth' => 'required|date|before:today',
        'form.gender' => 'required|in:male,female,other',
        'form.notes' => 'nullable|string|max:1000',
        'photo' => 'nullable|image|max:2048',
    ];

    public array $relationships = [
        'spouse' => 'Spouse',
        'child' => 'Child',
        'parent' => 'Parent',
        'sibling' => 'Sibling',
        'grandparent' => 'Grandparent',
        'grandchild' => 'Grandchild',
        'guardian' => 'Guardian',
        'dependent' => 'Dependent',
        'other' => 'Other',
    ];

    public array $sortOptions = [
        'created_at' => 'Date Added',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'relationship' => 'Relationship',
        'date_of_birth' => 'Date of Birth',
    ];

    public function mount(int $memberId): void
    {
        $this->authorize('membership.view_members');

        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        $this->member = Member::where('organization_id', $organizationId)
            ->findOrFail($memberId);

        $this->memberId = $memberId;
    }

    public function render()
    {
        $familyMembers = $this->getFamilyMembersQuery()
            ->paginate($this->perPage);

        return view('livewire.membership.family-member-manager', [
            'member' => $this->member,
            'familyMembers' => $familyMembers,
        ]);
    }

    protected function getFamilyMembersQuery()
    {
        $query = $this->member->familyMembers();

        // Apply search
        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('relationship', 'like', "%{$this->search}%")
                    ->orWhere('barcode_number', 'like', "%{$this->search}%");
            });
        }

        // Apply relationship filter
        if ($this->relationshipFilter !== 'all') {
            $query->byRelationship($this->relationshipFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return $query;
    }

    // Form Management
    public function save(): void
    {
        $this->authorize('membership.edit_members');

        $this->validate();

        DB::transaction(function () {
            if ($this->editingFamilyMember) {
                $familyMember = $this->member->familyMembers()
                    ->findOrFail($this->editingFamilyMember);

                $familyMember->update($this->form);

                $this->dispatch('family-member-updated', familyMemberId: $familyMember->id);
            } else {
                $familyMember = app(MembershipService::class)->addFamilyMember(
                    $this->member,
                    $this->form
                );

                $this->dispatch('family-member-saved', familyMemberId: $familyMember->id);
            }
        });

        $this->resetForm();
        $this->showForm = false;
    }

    public function edit(int $familyMemberId): void
    {
        $this->authorize('membership.edit_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $this->editingFamilyMember = $familyMemberId;
        $this->form = [
            'relationship' => $familyMember->relationship,
            'title' => $familyMember->title,
            'first_name' => $familyMember->first_name,
            'last_name' => $familyMember->last_name,
            'date_of_birth' => $familyMember->date_of_birth?->format('Y-m-d'),
            'gender' => $familyMember->gender,
            'notes' => $familyMember->notes ?? '',
        ];

        $this->showForm = true;
    }

    public function deleteFamilyMember(int $familyMemberId): void
    {
        $this->authorize('membership.delete_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        DB::transaction(function () use ($familyMember, $familyMemberId) {
            $familyMember->delete();

            $this->dispatch('family-member-deleted', familyMemberId: $familyMemberId);
        });
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function resetForm(): void
    {
        $this->form = [
            'relationship' => '',
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'notes' => '',
        ];
        $this->editingFamilyMember = null;
    }

    // Photo Management
    public function uploadPhoto(int $familyMemberId): void
    {
        $this->authorize('membership.edit_members');

        $this->validate(['photo' => 'nullable|image|max:2048']);

        if ($this->photo) {
            $familyMember = $this->member->familyMembers()
                ->findOrFail($familyMemberId);

            $path = $this->photo->store('family-member-photos', 'public');

            // Delete old photo if exists
            if ($familyMember->photo_path) {
                Storage::disk('public')->delete($familyMember->photo_path);
            }

            $familyMember->update(['photo_path' => $path]);

            $this->dispatch('photo-uploaded', familyMemberId: $familyMemberId);
            $this->reset('photo');
        }
    }

    public function removePhoto(int $familyMemberId): void
    {
        $this->authorize('membership.edit_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        if ($familyMember->photo_path) {
            Storage::disk('public')->delete($familyMember->photo_path);
            $familyMember->update(['photo_path' => null]);

            $this->dispatch('photo-removed', familyMemberId: $familyMemberId);
        }
    }

    // QR Code and Barcode Generation
    public function generateQRCode(int $familyMemberId): void
    {
        $this->authorize('membership.print_cards');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $qrData = [
            'type' => 'family_member',
            'id' => $familyMember->id,
            'primary_member_id' => $this->member->id,
            'barcode_number' => $familyMember->barcode_number,
            'name' => $familyMember->full_name,
            'relationship' => $familyMember->relationship,
        ];

        // Simple QR code placeholder for testing
        $this->qrCodeData = base64_encode('QR_CODE_PLACEHOLDER_'.json_encode($qrData));
        $this->showQRCode = true;

        $this->dispatch('qr-code-generated', familyMemberId: $familyMemberId);
    }

    public function generateBarcode(int $familyMemberId): void
    {
        $this->authorize('membership.print_cards');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        // Simple barcode placeholder for testing
        $this->barcodeData = base64_encode('BARCODE_PLACEHOLDER_'.$familyMember->barcode_number);
        $this->showBarcode = true;

        $this->dispatch('barcode-generated', familyMemberId: $familyMemberId);
    }

    public function downloadQRCode(int $familyMemberId)
    {
        $this->authorize('membership.print_cards');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $filename = "family_member_{$familyMember->id}_qrcode.png";

        // Simple QR code placeholder for testing
        $qrCodeData = 'QR_CODE_PLACEHOLDER_'.$familyMember->barcode_number;

        return response()->streamDownload(function () use ($qrCodeData) {
            echo $qrCodeData;
        }, $filename);
    }

    public function downloadBarcode(int $familyMemberId)
    {
        $this->authorize('membership.print_cards');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $filename = "family_member_{$familyMember->id}_barcode.png";

        // Simple barcode placeholder for testing
        $barcodeData = 'BARCODE_PLACEHOLDER_'.$familyMember->barcode_number;

        return response()->streamDownload(function () use ($barcodeData) {
            echo $barcodeData;
        }, $filename);
    }

    // Status Management
    public function activateFamilyMember(int $familyMemberId): void
    {
        $this->authorize('membership.manage_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $familyMember->update(['status' => 'active']);

        $this->dispatch('family-member-activated', familyMemberId: $familyMemberId);
    }

    public function deactivateFamilyMember(int $familyMemberId): void
    {
        $this->authorize('membership.manage_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $familyMember->update(['status' => 'inactive']);

        $this->dispatch('family-member-deactivated', familyMemberId: $familyMemberId);
    }

    // Sorting and Pagination
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

    public function updatedRelationshipFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    // Utility Methods
    public function getAgeFromDateOfBirth(string $dateOfBirth): int
    {
        return \Carbon\Carbon::parse($dateOfBirth)->age;
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'red',
            default => 'gray',
        };
    }

    #[On('refresh-family-members')]
    public function refreshFamilyMembers(): void
    {
        $this->resetPage();
    }

    public function getFamilyMemberStatistics(): array
    {
        $familyMembers = $this->member->familyMembers;

        return [
            'total' => $familyMembers->count(),
            'active' => $familyMembers->where('status', 'active')->count(),
            'inactive' => $familyMembers->where('status', 'inactive')->count(),
            'by_relationship' => $familyMembers->groupBy('relationship')->map->count(),
        ];
    }
}
