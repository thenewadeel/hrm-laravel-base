<?php

namespace App\Services\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Dompdf\Dompdf;
use Dompdf\Options;

class CardPrintingService
{
    /**
     * Generate member card HTML
     */
    public function generateMemberCard(Member $member, string $template = 'default'): string
    {
        $data = [
            'member' => $member,
            'barcode' => $this->generateBarcode($member->barcode_number),
            'organization' => $member->organization,
            'photo_url' => $member->photo_path ? Storage::url($member->photo_path) : null,
            'expiry_date' => $member->expiry_date ? $member->expiry_date->format('M Y') : null,
        ];
        
        return view('membership.cards.templates.' . $template, $data)->render();
    }

    /**
     * Generate family member card HTML
     */
    public function generateFamilyMemberCard(FamilyMember $familyMember, string $template = 'family'): string
    {
        $data = [
            'family_member' => $familyMember,
            'primary_member' => $familyMember->primaryMember,
            'barcode' => $this->generateBarcode($familyMember->barcode_number),
            'organization' => $familyMember->organization,
            'photo_url' => $familyMember->photo_path ? Storage::url($familyMember->photo_path) : null,
        ];
        
        return view('membership.cards.templates.' . $template, $data)->render();
    }

    /**
     * Create batch cards for multiple members
     */
    public function createBatchCards(array $memberIds, string $template = 'default'): string
    {
        $members = Member::with(['familyMembers', 'organization'])
            ->whereIn('id', $memberIds)
            ->get();
        
        $cardsHtml = '';
        
        foreach ($members as $member) {
            $cardsHtml .= $this->generateMemberCard($member, $template);
            
            // Add family member cards
            foreach ($member->familyMembers as $familyMember) {
                $cardsHtml .= $this->generateFamilyMemberCard($familyMember, 'family');
            }
        }
        
        return $cardsHtml;
    }

    /**
     * Export cards to PDF
     */
    public function exportCardsToPdf(string $htmlContent, array $options = []): string
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);
        $pdfOptions->set('isHtml5ParserEnabled', true);
        
        // Set paper size and orientation
        $pdfOptions->set('paperSize', $options['paper_size'] ?? 'business-card');
        $pdfOptions->set('orientation', $options['orientation'] ?? 'landscape');
        
        // Set margins
        $pdfOptions->set('marginLeft', 5);
        $pdfOptions->set('marginRight', 5);
        $pdfOptions->set('marginTop', 5);
        $pdfOptions->set('marginBottom', 5);
        
        $dompdf = new Dompdf($pdfOptions);
        $dompdf->loadHtml($htmlContent);
        $dompdf->render();
        
        // Generate filename
        $filename = 'membership-cards-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        
        // Save to storage
        $path = 'membership/cards/' . $filename;
        Storage::put($path, $dompdf->output());
        
        return $path;
    }

    /**
     * Generate single card PDF
     */
    public function generateSingleCardPdf(Member $member, string $template = 'default'): string
    {
        $html = $this->generateMemberCard($member, $template);
        return $this->exportCardsToPdf($html, [
            'paper_size' => 'business-card',
            'orientation' => 'landscape',
        ]);
    }

    /**
     * Generate barcode image
     */
    public function generateBarcode(string $barcodeNumber, array $options = []): string
    {
        // For now, return a placeholder - in real implementation, use a barcode library
        $width = $options['width'] ?? 200;
        $height = $options['height'] ?? 50;
        
        // Simple SVG barcode placeholder (replace with actual barcode library)
        $barcodeSvg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="white" stroke="black" stroke-width="1"/>
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="monospace" font-size="12">' . $barcodeNumber . '</text>
        </svg>';
        
        // Convert to base64 for embedding
        return 'data:image/svg+xml;base64,' . base64_encode($barcodeSvg);
    }

    /**
     * Generate QR code for member
     */
    public function generateQRCode(Member $member): string
    {
        $qrData = [
            'type' => 'member',
            'id' => $member->id,
            'barcode' => $member->barcode_number,
            'name' => $member->full_name,
            'membership_number' => $member->membership_number,
            'organization' => $member->organization->name,
        ];
        
        // For now, return a placeholder - in real implementation, use QR code library
        $qrSvg = '<svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="white" stroke="black" stroke-width="1"/>
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="monospace" font-size="8">QR Code</text>
        </svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
    }

    /**
     * Get available card templates
     */
    public function getAvailableTemplates(): array
    {
        return [
            'default' => [
                'name' => 'Standard Card',
                'description' => 'Clean, professional design with member details',
                'preview' => '/images/card-templates/default-preview.jpg',
            ],
            'premium' => [
                'name' => 'Premium Card',
                'description' => 'Elegant design with gold accents',
                'preview' => '/images/card-templates/premium-preview.jpg',
            ],
            'family' => [
                'name' => 'Family Card',
                'description' => 'Compact design for family members',
                'preview' => '/images/card-templates/family-preview.jpg',
            ],
            'minimal' => [
                'name' => 'Minimal Card',
                'description' => 'Simple, clean design with essential info only',
                'preview' => '/images/card-templates/minimal-preview.jpg',
            ],
        ];
    }

    /**
     * Validate card template
     */
    public function validateTemplate(string $template): bool
    {
        $availableTemplates = array_keys($this->getAvailableTemplates());
        return in_array($template, $availableTemplates);
    }

    /**
     * Get card printing settings
     */
    public function getPrintingSettings(): array
    {
        return [
            'paper_sizes' => [
                'business-card' => 'Business Card (90mm x 54mm)',
                'credit-card' => 'Credit Card (85mm x 54mm)',
                'a4' => 'A4 (210mm x 297mm)',
                'letter' => 'Letter (8.5" x 11")',
            ],
            'orientations' => [
                'landscape' => 'Landscape',
                'portrait' => 'Portrait',
            ],
            'quality_settings' => [
                'draft' => 'Draft (150 DPI)',
                'normal' => 'Normal (300 DPI)',
                'high' => 'High (600 DPI)',
            ],
        ];
    }

    /**
     * Preview card with template
     */
    public function previewCard(Member $member, string $template): array
    {
        if (!$this->validateTemplate($template)) {
            throw new \InvalidArgumentException('Invalid card template: ' . $template);
        }
        
        return [
            'html' => $this->generateMemberCard($member, $template),
            'template_info' => $this->getAvailableTemplates()[$template],
            'member_info' => [
                'name' => $member->full_name,
                'membership_number' => $member->membership_number,
                'barcode_number' => $member->barcode_number,
                'status' => $member->status,
                'expiry_date' => $member->expiry_date?->format('M Y'),
            ],
        ];
    }

    /**
     * Bulk generate cards for organization
     */
    public function bulkGenerateCards(int $organizationId, array $filters = []): string
    {
        $query = Member::where('organization_id', $organizationId)
            ->with(['familyMembers', 'organization']);
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['has_family'])) {
            if ($filters['has_family']) {
                $query->has('familyMembers');
            }
        }
        
        if (isset($filters['subscription_status'])) {
            $query->whereHas('subscriptions', function ($q) use ($filters) {
                $q->where('status', $filters['subscription_status']);
            });
        }
        
        $members = $query->get();
        
        $allCardsHtml = '';
        $template = $filters['template'] ?? 'default';
        
        foreach ($members as $member) {
            $allCardsHtml .= $this->generateMemberCard($member, $template);
            
            // Add family member cards if requested
            if ($filters['include_family'] ?? true) {
                foreach ($member->familyMembers as $familyMember) {
                    $allCardsHtml .= $this->generateFamilyMemberCard($familyMember, 'family');
                }
            }
        }
        
        return $this->exportCardsToPdf($allCardsHtml, [
            'paper_size' => $filters['paper_size'] ?? 'a4',
            'orientation' => $filters['orientation'] ?? 'landscape',
        ]);
    }

    /**
     * Get card generation statistics
     */
    public function getCardStatistics(int $organizationId): array
    {
        $totalMembers = Member::where('organization_id', $organizationId)->count();
        $activeMembers = Member::where('organization_id', $organizationId)->active()->count();
        $membersWithPhotos = Member::where('organization_id', $organizationId)
            ->whereNotNull('photo_path')
            ->count();
        
        $totalFamilyMembers = FamilyMember::where('organization_id', $organizationId)->count();
        $activeFamilyMembers = FamilyMember::where('organization_id', $organizationId)->active()->count();
        
        return [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'members_with_photos' => $membersWithPhotos,
            'photo_completion_rate' => $totalMembers > 0 ? round(($membersWithPhotos / $totalMembers) * 100, 2) : 0,
            'total_family_members' => $totalFamilyMembers,
            'active_family_members' => $activeFamilyMembers,
            'total_cards_needed' => $activeMembers + $activeFamilyMembers,
        ];
    }
}