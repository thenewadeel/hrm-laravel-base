<?php

namespace App\Livewire\Membership;

use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use Illuminate\Support\Collection;
use Livewire\Component;

class CardScanner extends Component
{
    public string $scannedBarcode = '';

    public ?Member $scannedMember = null;

    public ?FamilyMember $scannedFamilyMember = null;

    public string $accessStatus = '';

    public bool $accessGranted = false;

    public bool $showResult = false;

    public string $scanType = 'member'; // member or family

    public Collection $recentScans;

    public string $errorMessage = '';

    protected $rules = [
        'scannedBarcode' => 'required|string|min:3',
    ];

    public function mount()
    {
        $this->recentScans = collect();
        $this->loadRecentScans();
    }

    public function scanBarcode()
    {
        $this->validate();

        $this->reset(['scannedMember', 'scannedFamilyMember', 'accessStatus', 'accessGranted', 'showResult', 'errorMessage']);

        try {
            // Try to find as member first
            if ($this->scanType === 'member') {
                $member = Member::where('barcode_number', $this->scannedBarcode)
                    ->where('organization_id', auth()->user()->current_organization_id)
                    ->with(['familyMembers', 'currentSubscription'])
                    ->first();

                if ($member) {
                    $this->scannedMember = $member;
                    $this->processMemberAccess();
                } else {
                    // Try as family member
                    $familyMember = FamilyMember::where('barcode_number', $this->scannedBarcode)
                        ->whereHas('primaryMember', function ($query) {
                            $query->where('organization_id', auth()->user()->current_organization_id);
                        })
                        ->with('primaryMember')
                        ->first();

                    if ($familyMember) {
                        $this->scannedFamilyMember = $familyMember;
                        $this->scannedMember = $familyMember->primaryMember;
                        $this->processFamilyMemberAccess();
                    } else {
                        $this->errorMessage = 'Member not found. Please check the barcode and try again.';
                        $this->playErrorSound();
                    }
                }
            } else {
                // Family member scan mode
                $familyMember = FamilyMember::where('barcode_number', $this->scannedBarcode)
                    ->whereHas('primaryMember', function ($query) {
                        $query->where('organization_id', auth()->user()->current_organization_id);
                    })
                    ->with('primaryMember')
                    ->first();

                if ($familyMember) {
                    $this->scannedFamilyMember = $familyMember;
                    $this->scannedMember = $familyMember->primaryMember;
                    $this->processFamilyMemberAccess();
                } else {
                    $this->errorMessage = 'Family member not found. Please check the barcode and try again.';
                    $this->playErrorSound();
                }
            }

            if ($this->scannedMember) {
                $this->logAccessAttempt();
                $this->loadRecentScans();
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred during scanning. Please try again.';
            $this->playErrorSound();
        }

        $this->showResult = true;
    }

    private function processMemberAccess()
    {
        if (! $this->scannedMember) {
            return;
        }

        $member = $this->scannedMember;

        // Check membership status
        switch ($member->status) {
            case 'active':
                // Check if subscription is valid
                if ($member->currentSubscription && $member->currentSubscription->status === 'active') {
                    if ($member->currentSubscription->end_date >= now()) {
                        $this->accessGranted = true;
                        $this->accessStatus = 'Access Granted - Active Member';
                        $this->playSuccessSound();
                    } else {
                        $this->accessGranted = false;
                        $this->accessStatus = 'Access Denied - Subscription Expired';
                        $this->playWarningSound();
                    }
                } else {
                    $this->accessGranted = false;
                    $this->accessStatus = 'Access Denied - No Active Subscription';
                    $this->playWarningSound();
                }
                break;

            case 'suspended':
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Membership Suspended';
                $this->playWarningSound();
                break;

            case 'expired':
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Membership Expired';
                $this->playWarningSound();
                break;

            case 'inactive':
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Membership Inactive';
                $this->playWarningSound();
                break;

            default:
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Unknown Status';
                $this->playErrorSound();
                break;
        }
    }

    private function processFamilyMemberAccess()
    {
        if (! $this->scannedFamilyMember) {
            return;
        }

        $familyMember = $this->scannedFamilyMember;

        // Check family member status
        switch ($familyMember->status) {
            case 'active':
                // Check primary member's access
                $this->processMemberAccess();
                if ($this->accessGranted) {
                    $this->accessStatus = 'Access Granted - Active Family Member';
                } else {
                    $this->accessStatus = 'Access Denied - Primary Member '.str_replace('Access Denied - ', '', $this->accessStatus);
                }
                break;

            case 'suspended':
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Family Member Suspended';
                $this->playWarningSound();
                break;

            case 'expired':
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Family Member Expired';
                $this->playWarningSound();
                break;

            case 'inactive':
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Family Member Inactive';
                $this->playWarningSound();
                break;

            default:
                $this->accessGranted = false;
                $this->accessStatus = 'Access Denied - Unknown Family Member Status';
                $this->playErrorSound();
                break;
        }
    }

    private function logAccessAttempt()
    {
        // Log the access attempt for audit trail
        $logData = [
            'member_id' => $this->scannedMember->id,
            'family_member_id' => $this->scannedFamilyMember?->id,
            'barcode_number' => $this->scannedBarcode,
            'access_granted' => $this->accessGranted,
            'access_status' => $this->accessStatus,
            'scan_type' => $this->scanType,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        // Store in session for demo purposes (in production, this would go to database)
        $recentLogs = session('recent_access_logs', []);
        array_unshift($recentLogs, $logData);
        $recentLogs = array_slice($recentLogs, 0, 10); // Keep only last 10
        session(['recent_access_logs' => $recentLogs]);
    }

    private function loadRecentScans()
    {
        $logs = session('recent_access_logs', []);
        $this->recentScans = collect($logs)->take(5);
    }

    public function playSuccessSound()
    {
        $this->dispatch('play-sound', sound: 'success');
    }

    public function playWarningSound()
    {
        $this->dispatch('play-sound', sound: 'warning');
    }

    public function playErrorSound()
    {
        $this->dispatch('play-sound', sound: 'error');
    }

    public function clearScan()
    {
        $this->reset(['scannedBarcode', 'scannedMember', 'scannedFamilyMember', 'accessStatus', 'accessGranted', 'showResult', 'errorMessage']);
    }

    public function simulateScan($barcodeNumber)
    {
        $this->scannedBarcode = $barcodeNumber;
        $this->scanBarcode();
    }

    public function getDemoBarcodesProperty()
    {
        return [
            ['barcode' => 'BC197867', 'type' => 'John Anderson (Active)'],
            ['barcode' => 'BC968782', 'type' => 'Maria Garcia (Active)'],
            ['barcode' => 'BC696161', 'type' => 'Emma Anderson (Family)'],
            ['barcode' => 'BC-9072933686', 'type' => 'Arely Collier (Active)'],
            ['barcode' => 'INVALID', 'type' => 'Invalid Barcode'],
        ];
    }

    public function render()
    {
        return view('livewire.membership.card-scanner');
    }
}
