<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use App\Services\Membership\MembershipService;
use Livewire\Component;

class MembershipDemoHub extends Component
{
    public bool $isPlaying = false;

    public bool $isPaused = false;

    public int $currentStep = 0;

    public int $currentScenario = 0;

    public float $progress = 0.0;

    public string $demoMode = 'guided'; // guided, auto, manual

    public int $autoPlaySpeed = 3000; // milliseconds

    public array $demoStats = [
        'members_created' => 0,
        'cards_scanned' => 0,
        'fees_processed' => 0,
        'cards_generated' => 0,
        'bulk_uploaded' => 0,
    ];

    public array $scenarios = [
        [
            'id' => 'gate-access',
            'title' => 'Gate Access Control',
            'description' => 'Experience seamless member access through card scanning',
            'icon' => 'shield-check',
            'duration' => 45,
            'steps' => [
                ['title' => 'Welcome to Gate Access', 'description' => 'Members scan their cards for instant access', 'duration' => 5],
                ['title' => 'Card Scanning', 'description' => 'Scan member barcode for verification', 'duration' => 10],
                ['title' => 'Access Validation', 'description' => 'System validates membership status in real-time', 'duration' => 10],
                ['title' => 'Access Granted', 'description' => 'Green light indicates successful access', 'duration' => 5],
                ['title' => 'Access Log', 'description' => 'All access attempts are logged for security', 'duration' => 5],
                ['title' => 'Family Member Access', 'description' => 'Family members access through primary member validation', 'duration' => 10],
            ],
        ],
        [
            'id' => 'member-registration',
            'title' => 'New Member Registration',
            'description' => 'Complete member onboarding with family management',
            'icon' => 'user-plus',
            'duration' => 60,
            'steps' => [
                ['title' => 'Member Information', 'description' => 'Enter personal details and contact information', 'duration' => 10],
                ['title' => 'Photo Upload', 'description' => 'Capture member photo for ID card', 'duration' => 8],
                ['title' => 'Membership Details', 'description' => 'Set membership type and duration', 'duration' => 7],
                ['title' => 'Family Members', 'description' => 'Add family members with relationships', 'duration' => 10],
                ['title' => 'Address Information', 'description' => 'Complete address and contact details', 'duration' => 8],
                ['title' => 'Membership Card', 'description' => 'Generate unique membership card and barcode', 'duration' => 7],
                ['title' => 'Welcome Email', 'description' => 'Send welcome email with membership details', 'duration' => 5],
                ['title' => 'Registration Complete', 'description' => 'Member successfully added to system', 'duration' => 5],
            ],
        ],
        [
            'id' => 'bulk-upload',
            'title' => 'Bulk Corporate Upload',
            'description' => 'Efficiently upload multiple members from CSV files',
            'icon' => 'upload-cloud',
            'duration' => 50,
            'steps' => [
                ['title' => 'CSV Template', 'description' => 'Download pre-formatted CSV template', 'duration' => 5],
                ['title' => 'Data Preparation', 'description' => 'Prepare member data in spreadsheet format', 'duration' => 8],
                ['title' => 'File Upload', 'description' => 'Upload CSV file for processing', 'duration' => 7],
                ['title' => 'Column Mapping', 'description' => 'Map CSV columns to database fields', 'duration' => 10],
                ['title' => 'Data Validation', 'description' => 'System validates data and shows errors', 'duration' => 8],
                ['title' => 'Preview Import', 'description' => 'Review data before final import', 'duration' => 7],
                ['title' => 'Process Import', 'description' => 'Bulk import members into system', 'duration' => 5],
            ],
        ],
        [
            'id' => 'fee-management',
            'title' => 'Fee Management',
            'description' => 'Comprehensive fee tracking and payment processing',
            'icon' => 'credit-card',
            'duration' => 55,
            'steps' => [
                ['title' => 'Fee Types', 'description' => 'Various fee types: subscription, late fees, penalties', 'duration' => 8],
                ['title' => 'Create Fee', 'description' => 'Create new fee with amount and due date', 'duration' => 10],
                ['title' => 'Fee Assignment', 'description' => 'Assign fees to individual members', 'duration' => 7],
                ['title' => 'Payment Processing', 'description' => 'Process payments via multiple methods', 'duration' => 10],
                ['title' => 'Receipt Generation', 'description' => 'Generate automatic payment receipts', 'duration' => 5],
                ['title' => 'Overdue Management', 'description' => 'Track and manage overdue payments', 'duration' => 8],
                ['title' => 'Fee Reports', 'description' => 'Generate comprehensive fee reports', 'duration' => 7],
            ],
        ],
        [
            'id' => 'card-operations',
            'title' => 'Card Operations',
            'description' => 'Professional card design and printing capabilities',
            'icon' => 'id-card',
            'duration' => 50,
            'steps' => [
                ['title' => 'Card Templates', 'description' => 'Choose from multiple card designs', 'duration' => 8],
                ['title' => 'Design Customization', 'description' => 'Customize colors, fonts, and layout', 'duration' => 10],
                ['title' => 'Photo Integration', 'description' => 'Integrate member photos seamlessly', 'duration' => 7],
                ['title' => 'QR & Barcode', 'description' => 'Generate QR codes and barcodes', 'duration' => 8],
                ['title' => 'Preview Card', 'description' => 'Preview card before printing', 'duration' => 7],
                ['title' => 'Batch Printing', 'description' => 'Print multiple cards at once', 'duration' => 5],
                ['title' => 'Card Management', 'description' => 'Track card status and reprints', 'duration' => 5],
            ],
        ],
    ];

    public array $demoData = [
        'sample_members' => [
            ['name' => 'John Anderson', 'email' => 'john.anderson@email.com', 'type' => 'Individual'],
            ['name' => 'Maria Garcia', 'email' => 'maria.garcia@email.com', 'type' => 'Individual'],
            ['name' => 'Tech Corp Team', 'email' => 'hr@techcorp.com', 'type' => 'Corporate'],
        ],
        'sample_barcodes' => [
            'BC197867', 'BC968782', 'BC696161', 'BC-9072933686', 'BC456789',
        ],
        'sample_fees' => [
            ['type' => 'Annual Subscription', 'amount' => 299.99],
            ['type' => 'Monthly Fee', 'amount' => 29.99],
            ['type' => 'Late Payment Fee', 'amount' => 25.00],
        ],
    ];

    // View helper properties
    public array $currentScenarioData = [];

    public array $currentStepData = [];

    public function mount(): void
    {
        // Authorization check removed for demo accessibility
    }

    public function startDemo(): void
    {
        $this->isPlaying = true;
        $this->isPaused = false;
        $this->currentStep = 0;
        $this->currentScenario = 0;
        $this->progress = 0.0;
        $this->resetDemoStats();
        $this->updateProgress();

        if ($this->demoMode === 'auto') {
            $this->startAutoPlay();
        }
    }

    public function pauseDemo(): void
    {
        $this->isPaused = true;
    }

    public function resumeDemo(): void
    {
        $this->isPaused = false;
        if ($this->demoMode === 'auto') {
            $this->startAutoPlay();
        }
    }

    public function stopDemo(): void
    {
        $this->isPlaying = false;
        $this->isPaused = false;
        $this->currentStep = 0;
        $this->currentScenario = 0;
        $this->progress = 0.0;
    }

    public function nextStep(): void
    {
        $scenario = $this->scenarios[$this->currentScenario];
        $totalSteps = count($scenario['steps']);

        if ($this->currentStep < $totalSteps - 1) {
            $this->currentStep++;
            $this->updateProgress();
            $this->updateDemoStats();
        } else {
            $this->nextScenario();
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
            $this->updateProgress();
        } elseif ($this->currentScenario > 0) {
            $this->currentScenario--;
            $this->currentStep = count($this->scenarios[$this->currentScenario]['steps']) - 1;
            $this->updateProgress();
        }
    }

    public function nextScenario(): void
    {
        if ($this->currentScenario < count($this->scenarios) - 1) {
            $this->currentScenario++;
            $this->currentStep = 0;
            $this->updateProgress();
        } else {
            $this->completeDemo();
        }
    }

    public function jumpToScenario(int $scenarioIndex): void
    {
        if ($scenarioIndex >= 0 && $scenarioIndex < count($this->scenarios)) {
            $this->currentScenario = $scenarioIndex;
            $this->currentStep = 0;
            $this->updateProgress();
        }
    }

    public function jumpToStep(int $stepIndex): void
    {
        $scenario = $this->scenarios[$this->currentScenario];
        if ($stepIndex >= 0 && $stepIndex < count($scenario['steps'])) {
            $this->currentStep = $stepIndex;
            $this->updateProgress();
        }
    }

    private function startAutoPlay(): void
    {
        if (! $this->isPlaying || $this->isPaused) {
            return;
        }

        $this->dispatch('auto-play-tick');

        // Continue auto-play
        $this->nextStep();

        if ($this->isPlaying && ! $this->isPaused) {
            $this->dispatch('start-auto-play', delay: $this->autoPlaySpeed);
        }
    }

    private function updateProgress(): void
    {
        $totalScenarios = count($this->scenarios);
        $totalSteps = array_sum(array_map(fn ($s) => count($s['steps']), $this->scenarios));

        $completedSteps = 0;
        for ($i = 0; $i < $this->currentScenario; $i++) {
            $completedSteps += count($this->scenarios[$i]['steps']);
        }
        $completedSteps += $this->currentStep + 1;

        $this->progress = $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
    }

    private function updateDemoStats(): void
    {
        $scenarioId = $this->scenarios[$this->currentScenario]['id'];

        switch ($scenarioId) {
            case 'gate-access':
                $this->demoStats['cards_scanned']++;
                break;
            case 'member-registration':
                $this->demoStats['members_created']++;
                break;
            case 'bulk-upload':
                $this->demoStats['bulk_uploaded']++;
                break;
            case 'fee-management':
                $this->demoStats['fees_processed']++;
                break;
            case 'card-operations':
                $this->demoStats['cards_generated']++;
                break;
        }
    }

    private function completeDemo(): void
    {
        $this->isPlaying = false;
        $this->progress = 100.0;
        $this->dispatch('demo-completed', stats: $this->demoStats);
    }

    public function resetDemoStats(): void
    {
        $this->demoStats = [
            'members_created' => 0,
            'cards_scanned' => 0,
            'fees_processed' => 0,
            'cards_generated' => 0,
            'bulk_uploaded' => 0,
        ];
    }

    public function generateDemoData(MembershipService $membershipService): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;

            // Create sample members
            foreach ($this->demoData['sample_members'] as $memberData) {
                $member = $membershipService->createMember([
                    'organization_id' => $organizationId,
                    'first_name' => explode(' ', $memberData['name'])[0],
                    'last_name' => explode(' ', $memberData['name'])[1] ?? 'Member',
                    'email' => $memberData['email'],
                    'join_date' => now(),
                    'status' => 'active',
                    'membership_number' => 'DEMO'.rand(1000, 9999),
                    'barcode_number' => $this->demoData['sample_barcodes'][array_rand($this->demoData['sample_barcodes'])],
                ]);

                $this->demoStats['members_created']++;
            }

            $this->dispatch('demo-data-generated');
            $this->dispatch('show-notification', message: 'Demo data generated successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error generating demo data: '.$e->getMessage(), type: 'error');
        }
    }

    public function resetDemoData(): void
    {
        try {
            $organizationId = auth()->user()->current_organization_id;

            // Remove demo members (those with DEMO prefix in membership number)
            Member::where('organization_id', $organizationId)
                ->where('membership_number', 'like', 'DEMO%')
                ->delete();

            $this->resetDemoStats();
            $this->dispatch('demo-data-reset');
            $this->dispatch('show-notification', message: 'Demo data reset successfully', type: 'success');

        } catch (\Exception $e) {
            $this->dispatch('show-notification', message: 'Error resetting demo data: '.$e->getMessage(), type: 'error');
        }
    }

    public function getCurrentScenarioProperty(): array
    {
        return $this->scenarios[$this->currentScenario] ?? [
            'id' => '',
            'title' => '',
            'description' => '',
            'steps' => [],
            'duration' => 0,
        ];
    }

    public function getCurrentStepProperty(): array
    {
        $scenario = $this->currentScenario;
        $steps = $this->scenarios[$scenario]['steps'] ?? [];

        return $steps[$this->currentStep] ?? [
            'title' => '',
            'description' => '',
            'duration' => 0,
        ];
    }

    // Add public properties for view access
    public function currentScenarioData(): array
    {
        return $this->getCurrentScenarioProperty();
    }

    public function currentStepData(): array
    {
        return $this->getCurrentStepProperty();
    }

    public function getTotalStepsProperty(): int
    {
        return array_sum(array_map(fn ($s) => isset($s['steps']) ? count($s['steps']) : 0, $this->scenarios));
    }

    public function getCompletedStepsProperty(): int
    {
        $completed = 0;
        for ($i = 0; $i < $this->currentScenario; $i++) {
            $completed += isset($this->scenarios[$i]['steps']) ? count($this->scenarios[$i]['steps']) : 0;
        }

        return $completed + $this->currentStep + 1;
    }

    public function getEstimatedTimeProperty(): int
    {
        return array_sum(array_map(fn ($s) => $s['duration'] ?? 0, $this->scenarios));
    }

    public function getElapsedTimeProperty(): int
    {
        $elapsed = 0;
        for ($i = 0; $i < $this->currentScenario; $i++) {
            $elapsed += $this->scenarios[$i]['duration'] ?? 0;
        }

        $currentScenario = $this->scenarios[$this->currentScenario] ?? [];
        $steps = $currentScenario['steps'] ?? [];
        for ($i = 0; $i <= $this->currentStep; $i++) {
            $elapsed += $steps[$i]['duration'] ?? 0;
        }

        return $elapsed;
    }

    public function render()
    {
        // Make computed data available to view
        $this->currentScenarioData = $this->getCurrentScenarioProperty();
        $this->currentStepData = $this->getCurrentStepProperty();

        return view('livewire.membership.membership-demo-hub');
    }
}
