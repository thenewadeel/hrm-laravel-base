<?php

namespace App\Livewire\Membership;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Services\Membership\MembershipService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class MemberForm extends Component
{
    use WithFileUploads;

    public ?Member $member = null;

    public bool $editMode = false;

    #[Validate('required|string|max:10')]
    public string $title = '';

    #[Validate('required|string|max:100')]
    public string $first_name = '';

    #[Validate('required|string|max:100')]
    public string $last_name = '';

    #[Validate('nullable|date|before:today')]
    public ?string $date_of_birth = null;

    #[Validate('required|in:male,female,other')]
    public string $gender = 'male';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:50')]
    public ?string $phone = null;

    #[Validate('nullable|string|max:500')]
    public ?string $address = null;

    #[Validate('nullable|string|max:100')]
    public ?string $city = null;

    #[Validate('nullable|string|max:100')]
    public ?string $state = null;

    #[Validate('nullable|string|max:20')]
    public ?string $postal_code = null;

    #[Validate('nullable|string|max:100')]
    public ?string $country = null;

    #[Validate('required|date|before_or_equal:today')]
    public string $join_date = '';

    #[Validate('nullable|date|after:join_date')]
    public ?string $expiry_date = null;

    #[Validate('nullable|string|max:1000')]
    public ?string $notes = null;

    #[Validate('nullable|image|max:2048')]
    public $photo;

    // Family member fields
    public array $family_members = [];

    public function mount(?Member $member = null): void
    {
        // Force create mode for create route (check if route exists first)
        if (request()->route() && request()->routeIs('members.create')) {
            $member = null;
        }

        $this->member = $member;
        $this->editMode = $member !== null;

        // Only check authorization if user is authenticated
        if (auth()->check()) {
            if ($this->editMode) {
                $this->authorize('update', $member);
                $this->loadMemberData();
            } else {
                $this->authorize('create', Member::class);
                $this->join_date = now()->format('Y-m-d');
            }
        } else {
            // Set default join date for unauthenticated users (will be checked on save)
            $this->join_date = now()->format('Y-m-d');
        }
    }

    /**
     * Determine if authorization should be checked
     */
    private function shouldCheckAuthorization(): bool
    {
        // Don't check authorization in testing when accessed directly without route context
        if (app()->environment('testing') && ! request()->route()) {
            return false;
        }

        return true;
    }

    private function loadMemberData(): void
    {
        if (! $this->member) {
            return;
        }

        $this->title = $this->member->title ?? '';
        $this->first_name = $this->member->first_name ?? '';
        $this->last_name = $this->member->last_name ?? '';
        $this->date_of_birth = $this->member->date_of_birth?->format('Y-m-d');
        $this->gender = $this->member->gender ?? 'male';
        $this->email = $this->member->email ?? '';
        $this->phone = $this->member->phone;
        $this->address = $this->member->address;
        $this->city = $this->member->city;
        $this->state = $this->member->state;
        $this->postal_code = $this->member->postal_code;
        $this->country = $this->member->country;
        $this->join_date = $this->member->join_date?->format('Y-m-d') ?? '';
        $this->expiry_date = $this->member->expiry_date?->format('Y-m-d');
        $this->notes = $this->member->notes;

        // Load existing family members
        if ($this->member->relationLoaded('familyMembers')) {
            foreach ($this->member->familyMembers as $familyMember) {
                $this->family_members[] = [
                    'id' => $familyMember->id,
                    'relationship' => $familyMember->relationship,
                    'title' => $familyMember->title ?? '',
                    'first_name' => $familyMember->first_name,
                    'last_name' => $familyMember->last_name,
                    'date_of_birth' => $familyMember->date_of_birth?->format('Y-m-d'),
                    'gender' => $familyMember->gender ?? 'other',
                    'notes' => $familyMember->notes,
                    'remove' => false,
                ];
            }
        }
    }

    public function save(MembershipService $membershipService): void
    {
        // Check authorization first
        if ($this->editMode) {
            $this->authorize('update', $this->member);
        } else {
            $this->authorize('create', Member::class);
        }

        $this->validate();

        $memberData = [
            'organization_id' => auth()->user()->current_organization_id,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'join_date' => $this->join_date,
            'expiry_date' => $this->expiry_date,
            'notes' => $this->notes,
        ];

        if ($this->photo) {
            $memberData['photo_path'] = $this->photo->store('member-photos', 'public');
        }

        try {
            if ($this->editMode) {
                $member = $membershipService->updateMember($this->member, $memberData);
                $message = 'Member updated successfully';
                $event = 'member-updated';
            } else {
                $member = $membershipService->createMember($memberData);
                $message = 'Member created successfully';
                $event = 'member-created';
            }

            // Process family members
            $this->processFamilyMembers($membershipService, $member);

            $this->dispatch($event, memberId: $member->id);
            $this->dispatch('show-notification', message: $message, type: 'success');

            if (! $this->editMode) {
                $this->reset();
                $this->join_date = now()->format('Y-m-d');
            }

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    private function processFamilyMembers(MembershipService $membershipService, Member $member): void
    {
        foreach ($this->family_members as $familyMemberData) {
            if ($familyMemberData['remove']) {
                // Remove family member if it exists
                if (isset($familyMemberData['id'])) {
                    $familyMember = FamilyMember::find($familyMemberData['id']);
                    if ($familyMember && $familyMember->primary_member_id === $member->id) {
                        $familyMember->delete();
                    }
                }

                continue;
            }

            $familyMemberFields = [
                'relationship' => $familyMemberData['relationship'],
                'title' => $familyMemberData['title'],
                'first_name' => $familyMemberData['first_name'],
                'last_name' => $familyMemberData['last_name'],
                'date_of_birth' => $familyMemberData['date_of_birth'],
                'gender' => $familyMemberData['gender'],
                'notes' => $familyMemberData['notes'],
            ];

            if (isset($familyMemberData['id'])) {
                // Update existing family member
                $familyMember = FamilyMember::find($familyMemberData['id']);
                if ($familyMember && $familyMember->primary_member_id === $member->id) {
                    $familyMember->update($familyMemberFields);
                }
            } else {
                // Add new family member
                $membershipService->addFamilyMember($member, $familyMemberFields);
            }
        }
    }

    public function addFamilyMember(): void
    {
        $this->family_members[] = [
            'id' => null,
            'relationship' => '',
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => null,
            'gender' => 'other',
            'notes' => '',
            'remove' => false,
        ];
    }

    public function removeFamilyMember(int $index): void
    {
        if (isset($this->family_members[$index])) {
            $this->family_members[$index]['remove'] = true;
        }
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.membership.member-form', [
            'genders' => [
                'male' => 'Male',
                'female' => 'Female',
                'other' => 'Other',
            ],
            'relationships' => [
                'spouse' => 'Spouse',
                'child' => 'Child',
                'parent' => 'Parent',
                'sibling' => 'Sibling',
                'dependent' => 'Dependent',
                'other' => 'Other',
            ],
        ]);
    }
}
