<?php

namespace App\Services\Membership;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class CardPrintingService
{
    /**
     * Generate member card HTML
     */
    public function generateMemberCard(Member $member, string $template = 'modern', array $designSettings = []): string
    {
        $data = [
            'member' => $member,
            'barcode' => $this->generateBarcode($member->barcode_number),
            'qr_code' => $this->generateQRCode($member),
            'organization' => $member->organization,
            'photo_url' => $member->photo_path ? Storage::url($member->photo_path) : null,
            'expiry_date' => $member->expiry_date ? $member->expiry_date->format('M Y') : null,
            'design_settings' => array_merge($this->getDefaultDesignSettings(), $designSettings),
            'card_type' => $designSettings['card_type'] ?? 'standard',
        ];

        return view('membership.cards.templates.'.$template, $data)->render();
    }

    /**
     * Generate family member card HTML
     */
    public function generateFamilyMemberCard(FamilyMember $familyMember, string $template = 'family', array $designSettings = []): string
    {
        $data = [
            'family_member' => $familyMember,
            'primary_member' => $familyMember->primaryMember,
            'barcode' => $this->generateBarcode($familyMember->barcode_number),
            'qr_code' => $this->generateFamilyMemberQRCode($familyMember),
            'organization' => $familyMember->organization,
            'photo_url' => $familyMember->photo_path ? Storage::url($familyMember->photo_path) : null,
            'design_settings' => array_merge($this->getDefaultDesignSettings(), $designSettings),
        ];

        return view('membership.cards.templates.'.$template, $data)->render();
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
        $pdfOptions = new Options;
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
        $filename = 'membership-cards-'.now()->format('Y-m-d-H-i-s').'.pdf';

        // Save to storage
        $path = 'membership/cards/'.$filename;
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
        $barcodeSvg = '<svg width="'.$width.'" height="'.$height.'" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" fill="white" stroke="black" stroke-width="1"/>
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-family="monospace" font-size="12">'.$barcodeNumber.'</text>
        </svg>';

        // Convert to base64 for embedding
        return 'data:image/svg+xml;base64,'.base64_encode($barcodeSvg);
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
            'status' => $member->status,
            'expiry_date' => $member->expiry_date?->format('Y-m-d'),
        ];

        // Generate a more realistic QR code pattern
        $qrSvg = $this->generateQRCodePattern(json_encode($qrData));

        return 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
    }

    /**
     * Generate QR code for family member
     */
    public function generateFamilyMemberQRCode(FamilyMember $familyMember): string
    {
        $qrData = [
            'type' => 'family_member',
            'id' => $familyMember->id,
            'barcode' => $familyMember->barcode_number,
            'name' => $familyMember->full_name,
            'primary_member' => $familyMember->primaryMember->full_name,
            'organization' => $familyMember->organization->name,
            'relationship' => $familyMember->relationship,
        ];

        $qrSvg = $this->generateQRCodePattern(json_encode($qrData));

        return 'data:image/svg+xml;base64,'.base64_encode($qrSvg);
    }

    /**
     * Generate QR code SVG pattern
     */
    private function generateQRCodePattern(string $data): string
    {
        $size = 150;
        $moduleSize = 5;
        $modules = $size / $moduleSize;

        // Simple QR-like pattern generator
        $pattern = '';
        for ($y = 0; $y < $modules; $y++) {
            for ($x = 0; $x < $modules; $x++) {
                $filled = (ord($data[($x + $y) % strlen($data)]) % 2) === 0;
                $color = $filled ? '#000' : '#fff';
                $xPos = $x * $moduleSize;
                $yPos = $y * $moduleSize;
                $pattern .= "<rect x='{$xPos}' y='{$yPos}' width='{$moduleSize}' height='{$moduleSize}' fill='{$color}'/>";
            }
        }

        // Add position markers (corners)
        $positionMarker = '<rect x="0" y="0" width="35" height="35" fill="#000"/><rect x="5" y="5" width="25" height="25" fill="#fff"/><rect x="10" y="10" width="15" height="15" fill="#000"/>';

        return "<svg width='{$size}' height='{$size}' xmlns='http://www.w3.org/2000/svg'>
            <rect width='100%' height='100%' fill='white' stroke='black' stroke-width='1'/>
            {$positionMarker}
            <g transform='translate(115,0) scale(-1,1)'>{$positionMarker}</g>
            <g transform='translate(0,115) scale(1,-1)'>{$positionMarker}</g>
            {$pattern}
        </svg>";
    }

    /**
     * Get available card templates
     */
    public function getAvailableTemplates(): array
    {
        return [
            'modern' => [
                'name' => 'Modern Card',
                'description' => 'Contemporary design with clean lines and professional layout',
                'preview' => '/images/card-templates/modern-preview.jpg',
                'features' => ['Photo', 'QR Code', 'Barcode', 'Status Indicator'],
            ],
            'classic' => [
                'name' => 'Classic Card',
                'description' => 'Traditional design with elegant typography',
                'preview' => '/images/card-templates/classic-preview.jpg',
                'features' => ['Photo', 'Barcode', 'Embossed Effect'],
            ],
            'premium' => [
                'name' => 'Premium Card',
                'description' => 'Luxury design with gold accents and premium features',
                'preview' => '/images/card-templates/premium-preview.jpg',
                'features' => ['Photo', 'QR Code', 'Barcode', 'Premium Badge', 'Metallic Finish'],
            ],
            'family' => [
                'name' => 'Family Card',
                'description' => 'Compact design optimized for family members',
                'preview' => '/images/card-templates/family-preview.jpg',
                'features' => ['Photo', 'QR Code', 'Relationship Info'],
            ],
            'corporate' => [
                'name' => 'Corporate Card',
                'description' => 'Professional design for corporate memberships',
                'preview' => '/images/card-templates/corporate-preview.jpg',
                'features' => ['Photo', 'QR Code', 'Barcode', 'Department Info'],
            ],
            'minimal' => [
                'name' => 'Minimal Card',
                'description' => 'Simple, clean design with essential information only',
                'preview' => '/images/card-templates/minimal-preview.jpg',
                'features' => ['QR Code', 'Essential Info'],
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
        if (! $this->validateTemplate($template)) {
            throw new \InvalidArgumentException('Invalid card template: '.$template);
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

    /**
     * Get default design settings
     */
    public function getDefaultDesignSettings(): array
    {
        return [
            'primary_color' => '#1e40af',
            'secondary_color' => '#f59e0b',
            'font_family' => 'Arial',
            'layout' => 'horizontal',
            'show_photo' => true,
            'show_qr_code' => true,
            'show_barcode' => true,
            'show_expiry' => true,
            'card_type' => 'standard',
        ];
    }

    /**
     * Generate professional barcode
     */
    public function generateProfessionalBarcode(string $barcodeNumber, array $options = []): string
    {
        $width = $options['width'] ?? 200;
        $height = $options['height'] ?? 50;

        // Generate Code 128 barcode pattern
        $bars = $this->generateCode128Pattern($barcodeNumber);
        $barWidth = $width / strlen($bars);

        $svg = '<svg width="'.$width.'" height="'.$height.'" xmlns="http://www.w3.org/2000/svg">';
        $svg .= '<rect width="100%" height="100%" fill="white"/>';

        for ($i = 0; $i < strlen($bars); $i++) {
            if ($bars[$i] === '1') {
                $x = $i * $barWidth;
                $svg .= '<rect x="'.$x.'" y="5" width="'.$barWidth.'" height="'.($height - 15).'" fill="black"/>';
            }
        }

        // Add barcode number text
        $svg .= '<text x="50%" y="'.($height - 2).'" text-anchor="middle" font-family="monospace" font-size="10" fill="black">'.$barcodeNumber.'</text>';
        $svg .= '</svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }

    /**
     * Generate Code 128 barcode pattern
     */
    private function generateCode128Pattern(string $data): string
    {
        // Simplified Code 128 pattern generation
        $pattern = '';
        foreach (str_split($data) as $char) {
            $ascii = ord($char);
            $binary = str_pad(decbin($ascii), 8, '0', STR_PAD_LEFT);
            $pattern .= $binary;
        }

        // Add start/stop patterns
        return '11010010000'.$pattern.'1100011101011';
    }

    /**
     * Create card with advanced features
     */
    public function createAdvancedCard(Member $member, array $options = []): array
    {
        $template = $options['template'] ?? 'modern';
        $designSettings = array_merge($this->getDefaultDesignSettings(), $options['design_settings'] ?? []);

        $html = $this->generateMemberCard($member, $template, $designSettings);
        $qrCode = $this->generateQRCode($member);
        $barcode = $this->generateProfessionalBarcode($member->barcode_number);

        return [
            'html' => $html,
            'qr_code' => $qrCode,
            'barcode' => $barcode,
            'template' => $template,
            'design_settings' => $designSettings,
        ];
    }
}
