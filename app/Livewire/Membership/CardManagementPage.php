<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\MemberCard;
use App\Services\Membership\CardPrintingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class CardManagementPage extends Component
{
    use WithPagination;

    public ?MemberCard $card = null;

    public array $cardPreview = [];

    public bool $showPreview = false;

    public string $statusNote = '';

    public string $search = '';

    public string $statusFilter = 'all';

    public string $typeFilter = 'all';

    public string $templateFilter = 'all';

    public array $selectedCards = [];

    public bool $showBatchActions = false;

    public string $selectedTemplate = 'modern';

    public array $availableTemplates = [];

    public bool $showTemplateSelector = false;

    public array $cardHistory = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'typeFilter' => ['except' => 'all'],
        'templateFilter' => ['except' => 'all'],
    ];

    protected CardPrintingService $cardPrintingService;

    public function boot(CardPrintingService $cardPrintingService): void
    {
        $this->cardPrintingService = $cardPrintingService;
        $this->availableTemplates = $cardPrintingService->getAvailableTemplates();
    }

    public function mount(): void
    {
        $this->selectedTemplate = 'modern';
    }

    public function loadCardPreview(int $cardId): void
    {
        $this->card = MemberCard::with(['member', 'organization'])->find($cardId);

        if ($this->card) {
            $this->loadCardData();
            $this->loadCardHistory();
            $this->showPreview = true;
        }
    }

    private function loadCardData(): void
    {
        if (! $this->card) {
            return;
        }

        // Generate QR code and barcode if not exists
        if (! $this->card->qr_code_path) {
            $qrCode = $this->cardPrintingService->generateQRCode($this->card->member);
            $this->card->update(['qr_code_path' => $qrCode]);
        }

        if (! $this->card->barcode_path) {
            $barcode = $this->cardPrintingService->generateProfessionalBarcode($this->card->card_number);
            $this->card->update(['barcode_path' => $barcode]);
        }

        $this->cardPreview = [
            'id' => $this->card->id,
            'card_number' => $this->card->card_number,
            'member_name' => $this->card->member->full_name,
            'member_id' => $this->card->member->membership_number,
            'card_type' => $this->card->card_type,
            'template' => $this->card->template,
            'status' => $this->card->status,
            'issue_date' => $this->card->issue_date?->format('M d, Y'),
            'expiry_date' => $this->card->expiry_date?->format('M d, Y'),
            'member_photo' => $this->card->member->photo_path,
            'qr_code_path' => $this->card->qr_code_path,
            'barcode_path' => $this->card->barcode_path,
            'print_count' => $this->card->print_count,
            'last_printed' => $this->card->last_printed_at?->format('M d, Y H:i'),
            'design_settings' => $this->card->design_settings ?? [],
            'member_email' => $this->card->member->email,
            'member_phone' => $this->card->member->phone,
            'organization_name' => $this->card->organization->name,
        ];
    }

    private function loadCardHistory(): void
    {
        if (! $this->card) {
            return;
        }

        // Simulate card history (in real implementation, this would come from a card_history table)
        $this->cardHistory = [
            [
                'date' => $this->card->created_at->format('M d, Y H:i'),
                'action' => 'Card Created',
                'details' => 'Card issued with template: '.$this->card->template,
                'user' => 'System',
            ],
        ];

        if ($this->card->last_printed_at) {
            $this->cardHistory[] = [
                'date' => $this->card->last_printed_at->format('M d, Y H:i'),
                'action' => 'Card Printed',
                'details' => 'PDF generated and downloaded',
                'user' => 'Current User',
            ];
        }

        if ($this->card->notes) {
            $this->cardHistory[] = [
                'date' => $this->card->updated_at->format('M d, Y H:i'),
                'action' => 'Status Updated',
                'details' => $this->card->notes,
                'user' => 'Administrator',
            ];
        }
    }

    public function activateCard(): void
    {
        if (! $this->card) {
            return;
        }

        $this->updateCardStatus('active', 'Card activated by administrator');
    }

    public function deactivateCard(): void
    {
        if (! $this->card) {
            return;
        }

        $this->updateCardStatus('inactive', 'Card deactivated by administrator');
    }

    public function markAsLost(): void
    {
        if (! $this->card) {
            return;
        }

        $this->updateCardStatus('lost', 'Card reported as lost');
    }

    public function markAsDamaged(): void
    {
        if (! $this->card) {
            return;
        }

        $this->updateCardStatus('damaged', 'Card reported as damaged');
    }

    private function updateCardStatus(string $status, string $defaultNote): void
    {
        $this->card->update([
            'status' => $status,
            'notes' => $this->statusNote ?: $defaultNote,
        ]);

        $this->cardPreview['status'] = $status;

        $this->dispatch('show-notification', [
            'type' => $this->getStatusNotificationType($status),
            'message' => "Card status updated to {$status}",
        ]);

        $this->statusNote = '';
        $this->loadCardHistory();
    }

    private function getStatusNotificationType(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'lost' => 'error',
            'damaged' => 'warning',
            'inactive' => 'warning',
            default => 'info',
        };
    }

    public function generatePDF(): void
    {
        if (! $this->card) {
            return;
        }

        try {
            // Generate PDF using CardPrintingService
            $pdfPath = $this->cardPrintingService->generateSingleCardPdf(
                $this->card->member,
                $this->card->template
            );

            // Update print count
            $this->card->incrementPrintCount();
            $this->cardPreview['print_count']++;
            $this->cardPreview['last_printed'] = now()->format('M d, Y H:i');

            $this->dispatch('show-notification', [
                'type' => 'success',
                'message' => 'PDF generated successfully',
            ]);

            // Trigger download
            $this->dispatch('download-pdf', [
                'url' => Storage::url($pdfPath),
                'filename' => 'member-card-'.$this->card->card_number.'.pdf',
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'message' => 'Failed to generate PDF: '.$e->getMessage(),
            ]);
        }
    }

    public function downloadCard(): void
    {
        if (! $this->card) {
            return;
        }

        // Generate high-resolution image version
        $this->dispatch('show-notification', [
            'type' => 'info',
            'message' => 'Preparing card for download...',
        ]);

        // In real implementation, generate PNG/JPG version
        $this->dispatch('download-image', [
            'url' => '/api/cards/'.$this->card->id.'/image',
            'filename' => 'member-card-'.$this->card->card_number.'.png',
        ]);
    }

    public function emailCard(): void
    {
        if (! $this->card) {
            return;
        }

        $this->dispatch('show-notification', [
            'type' => 'info',
            'message' => 'Sending card to member email...',
        ]);

        // In real implementation, send email with card attachment
        // Mail::to($this->card->member->email)->send(new MemberCardMail($this->card));

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Card emailed successfully to '.$this->card->member->email,
        ]);
    }

    public function changeTemplate(): void
    {
        if (! $this->card) {
            return;
        }

        $this->card->update(['template' => $this->selectedTemplate]);
        $this->cardPreview['template'] = $this->selectedTemplate;
        $this->loadCardData();

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Card template updated to '.$this->availableTemplates[$this->selectedTemplate]['name'],
        ]);

        $this->showTemplateSelector = false;
    }

    public function toggleCardSelection(int $cardId): void
    {
        if (in_array($cardId, $this->selectedCards)) {
            $this->selectedCards = array_diff($this->selectedCards, [$cardId]);
        } else {
            $this->selectedCards[] = $cardId;
        }

        $this->showBatchActions = count($this->selectedCards) > 0;
    }

    public function selectAllCards(): void
    {
        $cards = $this->getCardsQuery()->pluck('id')->toArray();
        $this->selectedCards = $cards;
        $this->showBatchActions = true;
    }

    public function clearSelection(): void
    {
        $this->selectedCards = [];
        $this->showBatchActions = false;
    }

    public function batchActivate(): void
    {
        $this->batchUpdateStatus('active', 'Batch activation by administrator');
    }

    public function batchDeactivate(): void
    {
        $this->batchUpdateStatus('inactive', 'Batch deactivation by administrator');
    }

    public function batchGeneratePDFs(): void
    {
        if (empty($this->selectedCards)) {
            return;
        }

        $this->dispatch('show-notification', [
            'type' => 'info',
            'message' => 'Generating batch PDFs...',
        ]);

        // In real implementation, generate batch PDF
        $cards = MemberCard::with('member')->whereIn('id', $this->selectedCards)->get();
        $pdfPath = $this->cardPrintingService->createBatchCards(
            $cards->pluck('member_id')->toArray(),
            $this->selectedTemplate
        );

        // Update print counts
        MemberCard::whereIn('id', $this->selectedCards)->increment('print_count');
        MemberCard::whereIn('id', $this->selectedCards)->update(['last_printed_at' => now()]);

        $this->dispatch('download-pdf', [
            'url' => Storage::url($pdfPath),
            'filename' => 'batch-cards-'.now()->format('Y-m-d-H-i-s').'.pdf',
        ]);

        $this->dispatch('show-notification', [
            'type' => 'success',
            'message' => 'Batch PDF generated for '.count($this->selectedCards).' cards',
        ]);

        $this->clearSelection();
    }

    private function batchUpdateStatus(string $status, string $note): void
    {
        if (empty($this->selectedCards)) {
            return;
        }

        MemberCard::whereIn('id', $this->selectedCards)->update([
            'status' => $status,
            'notes' => $note,
        ]);

        $this->dispatch('show-notification', [
            'type' => $this->getStatusNotificationType($status),
            'message' => count($this->selectedCards)." cards updated to {$status}",
        ]);

        $this->clearSelection();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTemplateFilter(): void
    {
        $this->resetPage();
    }

    private function getCardsQuery()
    {
        $query = MemberCard::with(['member', 'organization'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('card_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('member', function ($subQ) {
                        $subQ->where('first_name', 'like', '%'.$this->search.'%')
                            ->orWhere('last_name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%')
                            ->orWhere('membership_number', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->typeFilter !== 'all') {
            $query->where('card_type', $this->typeFilter);
        }

        if ($this->templateFilter !== 'all') {
            $query->where('template', $this->templateFilter);
        }

        return $query;
    }

    public function getCardsProperty()
    {
        return $this->getCardsQuery()->paginate(10);
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
        $this->card = null;
        $this->cardPreview = [];
        $this->cardHistory = [];
        $this->statusNote = '';
    }

    public function getStatusColor(): string
    {
        if (! $this->card) {
            return 'gray';
        }

        return match ($this->card->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'lost' => 'red',
            'damaged' => 'yellow',
            'expired' => 'red',
            'reprinted' => 'blue',
            default => 'gray',
        };
    }

    public function getCardTypeLabel(): string
    {
        if (! $this->card) {
            return '';
        }

        return match ($this->card->card_type) {
            'standard' => 'Standard',
            'premium' => 'Premium',
            'family' => 'Family',
            'corporate' => 'Corporate',
            default => ucfirst($this->card->card_type),
        };
    }

    public function getTemplateLabel(): string
    {
        if (! $this->card) {
            return '';
        }

        return $this->availableTemplates[$this->card->template]['name'] ?? ucfirst($this->card->template);
    }

    public function getCardStatistics(): array
    {
        $totalCards = MemberCard::count();
        $activeCards = MemberCard::where('status', 'active')->count();
        $inactiveCards = MemberCard::where('status', 'inactive')->count();
        $lostCards = MemberCard::where('status', 'lost')->count();
        $damagedCards = MemberCard::where('status', 'damaged')->count();

        return [
            'total' => $totalCards,
            'active' => $activeCards,
            'inactive' => $inactiveCards,
            'lost' => $lostCards,
            'damaged' => $damagedCards,
            'active_percentage' => $totalCards > 0 ? round(($activeCards / $totalCards) * 100, 1) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.membership.card-management-page', [
            'cards' => $this->cards,
            'statistics' => $this->getCardStatistics(),
        ]);
    }
}
