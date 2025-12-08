<?php

namespace App\Livewire\Membership;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Models\Membership\MemberCard;
use App\Services\Membership\CardPrintingService;
use Livewire\Component;
use Livewire\WithPagination;

class CardDesigner extends Component
{
    use WithPagination;

    public ?Member $member = null;

    public ?FamilyMember $familyMember = null;

    public ?MemberCard $memberCard = null;

    public string $cardTemplate = 'modern';

    public string $cardType = 'standard';

    public bool $previewMode = false;

    public string $previewHtml = '';

    public bool $batchMode = false;

    public array $selectedMembers = [];

    public array $batchFilters = [
        'status' => 'active',
        'include_family' => true,
        'template' => 'modern',
        'paper_size' => 'business-card',
        'orientation' => 'landscape',
    ];

    public array $designSettings = [
        'primary_color' => '#1e40af',
        'secondary_color' => '#f59e0b',
        'font_family' => 'Arial',
        'layout' => 'horizontal',
        'show_photo' => true,
        'show_qr_code' => true,
        'show_barcode' => true,
        'show_expiry' => true,
    ];

    public array $availableTemplates = [
        'modern' => ['name' => 'Modern Card', 'description' => 'Contemporary design with clean lines'],
        'classic' => ['name' => 'Classic Card', 'description' => 'Traditional design with elegant typography'],
        'premium' => ['name' => 'Premium Card', 'description' => 'Luxury design with gold accents'],
        'family' => ['name' => 'Family Card', 'description' => 'Compact design for family members'],
        'corporate' => ['name' => 'Corporate Card', 'description' => 'Professional design for corporate memberships'],
        'minimal' => ['name' => 'Minimal Card', 'description' => 'Simple, clean design with essential information'],
    ];

    public array $availableCardTypes = [
        'standard' => 'Standard',
        'premium' => 'Premium',
        'family' => 'Family',
        'corporate' => 'Corporate',
    ];

    public function mount(?Member $member = null, ?FamilyMember $familyMember = null, ?MemberCard $memberCard = null): void
    {
        $this->member = $member;
        $this->familyMember = $familyMember;
        $this->memberCard = $memberCard;

        if ($memberCard) {
            $this->member = $memberCard->member;
            $this->cardTemplate = $memberCard->template ?? 'modern';
            $this->cardType = $memberCard->card_type ?? 'standard';
            $this->designSettings = array_merge($this->designSettings, $memberCard->design_settings ?? []);
        }

        // $this->authorize('membership.print_cards');
    }

    public function render(CardPrintingService $cardService)
    {
        $organizationId = auth()->user()->current_organization_id;

        return view('livewire.membership.card-designer', [
            'cardStatistics' => $cardService->getCardStatistics($organizationId),
            'availableTemplates' => $cardService->getAvailableTemplates(),
            'printingSettings' => $cardService->getPrintingSettings(),
            'memberCards' => $this->batchMode ?
                MemberCard::where('organization_id', $organizationId)
                    ->with('member')
                    ->latest()
                    ->paginate(10) :
                collect(),
        ]);
    }

    public function generatePreview(CardPrintingService $cardService): void
    {
        try {
            if ($this->member) {
                $this->previewHtml = $cardService->generateMemberCard($this->member, $this->cardTemplate, $this->designSettings);
            } elseif ($this->familyMember) {
                $this->previewHtml = $cardService->generateFamilyMemberCard($this->familyMember, $this->cardTemplate, $this->designSettings);
            } else {
                throw new \Exception('No member or family member selected');
            }

            $this->previewMode = true;
            $this->dispatch('preview-generated');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error generating preview: '.$e->getMessage(), type: 'error');
        }
    }

    public function printCard(CardPrintingService $cardService): void
    {
        try {
            if ($this->member) {
                $pdfPath = $cardService->exportCardsToPdf(
                    $cardService->generateMemberCard($this->member, $this->cardTemplate),
                    [
                        'filename' => "member-card-{$this->member->id}.pdf",
                        'orientation' => 'landscape',
                    ]
                );
            } elseif ($this->familyMember) {
                $pdfPath = $cardService->exportCardsToPdf(
                    $cardService->generateFamilyMemberCard($this->familyMember, $this->cardTemplate),
                    [
                        'filename' => "family-member-card-{$this->familyMember->id}.pdf",
                        'orientation' => 'landscape',
                    ]
                );
            } else {
                throw new \Exception('No member or family member selected');
            }

            $this->dispatch('card-printed', path: $pdfPath);
            $this->dispatch('show-notification', message: 'Card generated successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error generating card: '.$e->getMessage(), type: 'error');
        }
    }

    public function downloadCard(CardPrintingService $cardService): void
    {
        try {
            if ($this->member) {
                $html = $cardService->generateMemberCard($this->member, $this->cardTemplate);
                $filename = "member-card-{$this->member->membership_number}.pdf";
            } elseif ($this->familyMember) {
                $html = $cardService->generateFamilyMemberCard($this->familyMember, $this->cardTemplate);
                $filename = "family-member-card-{$this->familyMember->barcode_number}.pdf";
            } else {
                throw new \Exception('No member or family member selected');
            }

            $pdfPath = $cardService->exportCardsToPdf($html, [
                'filename' => $filename,
                'orientation' => 'landscape',
            ]);

            $this->dispatch('download-card', path: $pdfPath, filename: $filename);

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error downloading card: '.$e->getMessage(), type: 'error');
        }
    }

    public function resetPreview(): void
    {
        $this->previewMode = false;
        $this->previewHtml = '';
    }

    public function updatedCardTemplate(): void
    {
        $this->resetPreview();
    }

    public function getPersonProperty()
    {
        return $this->member ?? $this->familyMember;
    }

    public function getPersonTypeProperty(): string
    {
        return $this->member ? 'member' : 'family_member';
    }

    public function getPersonNameProperty(): string
    {
        if ($this->member) {
            return $this->member->full_name;
        } elseif ($this->familyMember) {
            return $this->familyMember->full_name;
        }

        return 'Unknown';
    }

    public function getBarcodeNumberProperty(): string
    {
        if ($this->member) {
            return $this->member->barcode_number ?? '';
        } elseif ($this->familyMember) {
            return $this->familyMember->barcode_number ?? '';
        }

        return '';
    }

    public function saveCard(CardPrintingService $cardService): void
    {
        try {
            if (! $this->member) {
                throw new \Exception('No member selected');
            }

            $user = auth()->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }

            $organizationId = $user->current_organization_id;
            if (! $organizationId) {
                throw new \Exception('No organization selected');
            }

            $cardData = [
                'organization_id' => $organizationId,
                'member_id' => $this->member->id,
                'card_number' => 'CARD-'.strtoupper(uniqid()),
                'card_type' => $this->cardType,
                'template' => $this->cardTemplate,
                'status' => 'active',
                'issue_date' => now(),
                'expiry_date' => $this->member->expiry_date,
                'qr_code_path' => 'qr-codes/'.uniqid().'.png',
                'barcode_path' => 'barcodes/'.uniqid().'.png',
                'design_settings' => $this->designSettings,
                'print_count' => 0,
            ];

            $memberCard = MemberCard::create($cardData);

            $this->memberCard = $memberCard;
            $this->dispatch('card-saved', cardId: $memberCard->id);
            $this->dispatch('show-notification', message: 'Card saved successfully', type: 'success');

        } catch (\Exception $e) {
            logger()->error('Error saving card: '.$e->getMessage(), [
                'member_id' => $this->member?->id,
                'card_type' => $this->cardType,
                'template' => $this->cardTemplate,
                'user_id' => auth()->id(),
                'org_id' => auth()->user()?->current_organization_id,
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('show-notification', message: 'Error saving card: '.$e->getMessage(), type: 'error');
        }
    }

    public function reprintCard(CardPrintingService $cardService): void
    {
        try {
            if (! $this->memberCard) {
                throw new \Exception('No card selected for reprint');
            }

            $this->memberCard->incrementPrintCount();
            $this->memberCard->update(['last_printed_at' => now()]);

            $this->printCard($cardService);
            $this->dispatch('show-notification', message: 'Card reprinted successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error reprinting card: '.$e->getMessage(), type: 'error');
        }
    }

    public function generateBatchCards(CardPrintingService $cardService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;
            $pdfPath = $cardService->bulkGenerateCards($organizationId, $this->batchFilters);

            $this->dispatch('batch-cards-generated', path: $pdfPath);
            $this->dispatch('show-notification', message: 'Batch cards generated successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error generating batch cards: '.$e->getMessage(), type: 'error');
        }
    }

    public function toggleBatchMode(): void
    {
        $this->batchMode = ! $this->batchMode;
        $this->resetPreview();
    }

    public function updateDesignSettings(string $key, $value): void
    {
        $this->designSettings[$key] = $value;
        $this->resetPreview();
    }

    public function generateQRCode(CardPrintingService $cardService): string
    {
        if ($this->member) {
            return $cardService->generateQRCode($this->member);
        }

        return '';
    }

    public function getCardStatusColorAttribute(): string
    {
        if (! $this->memberCard) {
            return 'gray';
        }

        return $this->memberCard->status_color;
    }

    public function getCardStatusLabelAttribute(): string
    {
        if (! $this->memberCard) {
            return 'No Card';
        }

        return $this->memberCard->status_label;
    }

    public function getIsCardActiveProperty(): bool
    {
        return $this->memberCard?->isActive() ?? false;
    }

    public function getIsCardExpiredProperty(): bool
    {
        return $this->memberCard?->isExpired() ?? false;
    }

    public function getIsCardExpiringSoonProperty(): bool
    {
        return $this->memberCard?->isExpiringSoon() ?? false;
    }
}
