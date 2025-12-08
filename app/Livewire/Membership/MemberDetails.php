<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberCard;
use App\Services\Membership\MembershipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MemberDetails extends Component
{
    use WithFileUploads;

    public int $memberId;

    public Member $member;

    // Family Member Management
    public bool $showAddFamilyMember = false;

    public bool $showEditFamilyMember = false;

    public int $editingFamilyMember = null;

    public array $familyMemberForm = [
        'relationship' => '',
        'title' => '',
        'first_name' => '',
        'last_name' => '',
        'date_of_birth' => '',
        'gender' => '',
        'notes' => '',
    ];

    // Communication
    public bool $showEmailModal = false;

    public bool $showSMSModal = false;

    public string $emailSubject = '';

    public string $emailMessage = '';

    public string $smsMessage = '';

    // Photo Upload
    public $photo;

    // UI State
    public bool $showQRCode = false;

    public bool $showBarcode = false;

    public string $qrCodeData = '';

    public string $barcodeData = '';

    protected $rules = [
        'familyMemberForm.relationship' => 'required|string|max:50',
        'familyMemberForm.title' => 'nullable|string|max:10',
        'familyMemberForm.first_name' => 'required|string|max:100',
        'familyMemberForm.last_name' => 'required|string|max:100',
        'familyMemberForm.date_of_birth' => 'required|date|before:today',
        'familyMemberForm.gender' => 'required|in:male,female,other',
        'familyMemberForm.notes' => 'nullable|string|max:1000',
        'emailSubject' => 'required|string|max:255',
        'emailMessage' => 'required|string|max:2000',
        'smsMessage' => 'required|string|max:160',
        'photo' => 'nullable|image|max:2048',
    ];

    public function mount(int $memberId): void
    {
        $this->authorize('membership.view_members');

        $user = auth()->user();
        $organizationId = $user->current_organization_id ??
                         $user->operating_organization_id ??
                         $user->organizations()->first()?->id;

        $this->member = Member::where('organization_id', $organizationId)
            ->with(['familyMembers', 'subscriptions', 'fees', 'cards'])
            ->findOrFail($memberId);

        $this->memberId = $memberId;
    }

    public function render()
    {
        return view('livewire.membership.member-details', [
            'member' => $this->member->fresh(['familyMembers', 'subscriptions', 'fees', 'cards']),
        ]);
    }

    // Family Member Management
    public function addFamilyMember(): void
    {
        $this->authorize('membership.edit_members');

        $this->validate();

        DB::transaction(function () {
            $familyMember = app(MembershipService::class)->addFamilyMember(
                $this->member,
                $this->familyMemberForm
            );

            $this->dispatch('family-member-added', familyMemberId: $familyMember->id);
            $this->resetFamilyMemberForm();
            $this->showAddFamilyMember = false;
        });
    }

    public function editFamilyMember(int $familyMemberId): void
    {
        $this->authorize('membership.edit_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        $this->editingFamilyMember = $familyMemberId;
        $this->familyMemberForm = [
            'relationship' => $familyMember->relationship,
            'title' => $familyMember->title,
            'first_name' => $familyMember->first_name,
            'last_name' => $familyMember->last_name,
            'date_of_birth' => $familyMember->date_of_birth?->format('Y-m-d'),
            'gender' => $familyMember->gender,
            'notes' => $familyMember->notes ?? '',
        ];

        $this->showEditFamilyMember = true;
    }

    public function updateFamilyMember(): void
    {
        $this->authorize('membership.edit_members');

        $this->validate();

        $familyMember = $this->member->familyMembers()
            ->findOrFail($this->editingFamilyMember);

        DB::transaction(function () use ($familyMember) {
            $familyMember->update($this->familyMemberForm);

            $this->dispatch('family-member-updated', familyMemberId: $familyMember->id);
            $this->resetFamilyMemberForm();
            $this->showEditFamilyMember = false;
            $this->editingFamilyMember = null;
        });
    }

    public function removeFamilyMember(int $familyMemberId): void
    {
        $this->authorize('membership.delete_members');

        $familyMember = $this->member->familyMembers()
            ->findOrFail($familyMemberId);

        DB::transaction(function () use ($familyMember) {
            $familyMember->delete();

            $this->dispatch('family-member-removed', familyMemberId: $familyMemberId);
        });
    }

    protected function resetFamilyMemberForm(): void
    {
        $this->familyMemberForm = [
            'relationship' => '',
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'date_of_birth' => '',
            'gender' => '',
            'notes' => '',
        ];
    }

    // Communication Methods
    public function sendEmail(): void
    {
        $this->authorize('membership.manage_members');

        $this->validate(['emailSubject' => 'required', 'emailMessage' => 'required']);

        // Send email logic here
        // Mail::to($this->member->email)->send(new MemberEmail($this->emailSubject, $this->emailMessage));

        $this->dispatch('email-sent', memberId: $this->memberId);
        $this->reset(['emailSubject', 'emailMessage']);
        $this->showEmailModal = false;
    }

    public function sendSMS(): void
    {
        $this->authorize('membership.manage_members');

        $this->validate(['smsMessage' => 'required']);

        // Send SMS logic here
        // SMS::send($this->member->phone, $this->smsMessage);

        $this->dispatch('sms-sent', memberId: $this->memberId);
        $this->reset(['smsMessage']);
        $this->showSMSModal = false;
    }

    // Photo Management
    public function uploadPhoto(): void
    {
        $this->authorize('membership.edit_members');

        $this->validate(['photo' => 'nullable|image|max:2048']);

        if ($this->photo) {
            $path = $this->photo->store('member-photos', 'public');

            // Delete old photo if exists
            if ($this->member->photo_path) {
                Storage::disk('public')->delete($this->member->photo_path);
            }

            $this->member->update(['photo_path' => $path]);

            $this->dispatch('photo-uploaded', memberId: $this->memberId);
            $this->reset('photo');
        }
    }

    public function removePhoto(): void
    {
        $this->authorize('membership.edit_members');

        if ($this->member->photo_path) {
            Storage::disk('public')->delete($this->member->photo_path);
            $this->member->update(['photo_path' => null]);

            $this->dispatch('photo-removed', memberId: $this->memberId);
        }
    }

    // QR Code and Barcode Generation
    public function generateQRCode(): void
    {
        $this->authorize('membership.print_cards');

        $qrData = [
            'type' => 'member',
            'id' => $this->member->id,
            'membership_number' => $this->member->membership_number,
            'barcode_number' => $this->member->barcode_number,
            'name' => $this->member->full_name,
            'status' => $this->member->status,
        ];

        $this->qrCodeData = base64_encode(QrCode::format('png')->size(200)->generate(json_encode($qrData)));
        $this->showQRCode = true;

        $this->dispatch('qr-code-generated', memberId: $this->memberId);
    }

    public function generateBarcode(): void
    {
        $this->authorize('membership.print_cards');

        $generator = new BarcodeGeneratorPNG;
        $this->barcodeData = base64_encode($generator->getBarcode($this->member->barcode_number, $generator::TYPE_CODE_128));
        $this->showBarcode = true;

        $this->dispatch('barcode-generated', memberId: $this->memberId);
    }

    public function downloadQRCode(): void
    {
        $this->authorize('membership.print_cards');

        $filename = "member_{$this->member->membership_number}_qrcode.png";
        $qrCode = QrCode::format('png')->size(300)->generate($this->member->barcode_number);

        return response()->streamDownload(function () use ($qrCode) {
            echo $qrCode;
        }, $filename);
    }

    public function downloadBarcode(): void
    {
        $this->authorize('membership.print_cards');

        $generator = new BarcodeGeneratorPNG;
        $barcode = $generator->getBarcode($this->member->barcode_number, $generator::TYPE_CODE_128);

        $filename = "member_{$this->member->membership_number}_barcode.png";

        return response()->streamDownload(function () use ($barcode) {
            echo $barcode;
        }, $filename);
    }

    // Member Status Management
    public function activateMember(): void
    {
        $this->authorize('membership.manage_members');

        app(MembershipService::class)->reactivateMember($this->member);
        $this->dispatch('member-activated', memberId: $this->memberId);
    }

    public function deactivateMember(): void
    {
        $this->authorize('membership.manage_members');

        app(MembershipService::class)->deactivateMember($this->member);
        $this->dispatch('member-deactivated', memberId: $this->memberId);
    }

    public function suspendMember(): void
    {
        $this->authorize('membership.manage_members');

        app(MembershipService::class)->suspendMember($this->member);
        $this->dispatch('member-suspended', memberId: $this->memberId);
    }

    // Card Management
    public function printMemberCard(): void
    {
        $this->authorize('membership.print_cards');

        $this->dispatch('print-member-card', memberId: $this->memberId);
    }

    public function generateMemberCard(): void
    {
        $this->authorize('membership.design_cards');

        DB::transaction(function () {
            $card = MemberCard::create([
                'organization_id' => $this->member->organization_id,
                'member_id' => $this->member->id,
                'card_number' => 'CARD-'.Str::random(10),
                'card_type' => 'standard',
                'template' => 'default',
                'status' => 'active',
                'issue_date' => now(),
                'expiry_date' => $this->member->expiry_date,
                'print_count' => 0,
            ]);

            $this->dispatch('member-card-generated', cardId: $card->id);
        });
    }

    // Utility Methods
    public function getAgeFromDateOfBirth(string $dateOfBirth): int
    {
        return \Carbon\Carbon::parse($dateOfBirth)->age;
    }

    public function formatCurrency(float $amount): string
    {
        return number_format($amount, 2);
    }

    public function getSubscriptionStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'expired' => 'red',
            'cancelled' => 'gray',
            'pending' => 'yellow',
            default => 'gray',
        };
    }

    public function getMemberStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'inactive' => 'gray',
            'suspended' => 'red',
            'expired' => 'yellow',
            default => 'gray',
        };
    }

    #[On('refresh-member-details')]
    public function refreshMember(): void
    {
        $this->member->refresh(['familyMembers', 'subscriptions', 'fees', 'cards']);
    }
}
