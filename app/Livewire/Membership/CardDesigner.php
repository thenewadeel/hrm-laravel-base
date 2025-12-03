<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use App\Services\Membership\CardPrintingService;
use Livewire\Component;

class CardDesigner extends Component
{
    public ?Member $member = null;
    public ?FamilyMember $familyMember = null;
    public string $cardTemplate = 'default';
    public bool $previewMode = false;
    public string $previewHtml = '';
    public array $availableTemplates = [
        'default' => 'Default Card',
        'premium' => 'Premium Card',
        'family' => 'Family Card',
        'corporate' => 'Corporate Card',
    ];

    public function mount(?Member $member = null, ?FamilyMember $familyMember = null): void
    {
        $this->member = $member;
        $this->familyMember = $familyMember;
        $this->authorize('membership.print_cards');
    }

    public function render(CardPrintingService $cardService)
    {
        return view('livewire.membership.card-designer', [
            'cardStatistics' => $cardService->getCardStatistics(auth()->user()->current_organization_id),
        ]);
    }

    public function generatePreview(CardPrintingService $cardService): void
    {
        try {
            if ($this->member) {
                $this->previewHtml = $cardService->generateMemberCard($this->member, $this->cardTemplate);
            } elseif ($this->familyMember) {
                $this->previewHtml = $cardService->generateFamilyMemberCard($this->familyMember, $this->cardTemplate);
            } else {
                throw new \Exception('No member or family member selected');
            }

            $this->previewMode = true;
            $this->dispatch('preview-generated');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error generating preview: ' . $e->getMessage(), type: 'error');
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
            $this->dispatch('show-notification', message: 'Error generating card: ' . $e->getMessage(), type: 'error');
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
            $this->dispatch('show-notification', message: 'Error downloading card: ' . $e->getMessage(), type: 'error');
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
            return $this->member->barcode_number;
        } elseif ($this->familyMember) {
            return $this->familyMember->barcode_number;
        }

        return '';
    }
}
