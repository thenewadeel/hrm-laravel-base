<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use Livewire\Component;

class SimpleScanner extends Component
{
    public string $scanInput = '';

    public array $recentScans = [];

    public bool $isScanning = false;

    public function mount(): void
    {
        $this->recentScans = [
            [
                'name' => 'John Doe',
                'member_id' => 'MEM001',
                'time' => '14:30:22',
                'success' => true,
            ],
            [
                'name' => 'Jane Smith',
                'member_id' => 'MEM002',
                'time' => '14:28:15',
                'success' => true,
            ],
            [
                'name' => 'Unknown',
                'member_id' => 'INVALID123',
                'time' => '14:25:08',
                'success' => false,
            ],
        ];
    }

    public function scan(): void
    {
        $this->isScanning = true;

        // Simulate scanning delay
        usleep(500000); // 0.5 second delay

        $member = Member::where('barcode_number', $this->scanInput)
            ->orWhere('membership_number', $this->scanInput)
            ->first();

        if ($member) {
            $this->recentScans[] = [
                'id' => $member->id,
                'name' => $member->first_name.' '.$member->last_name,
                'member_id' => $member->membership_number,
                'status' => $member->status,
                'time' => now()->format('H:i:s'),
                'success' => true,
            ];

            $this->dispatch('scan-success', member: $member->full_name);
        } else {
            $this->recentScans[] = [
                'name' => 'Unknown',
                'member_id' => $this->scanInput,
                'time' => now()->format('H:i:s'),
                'success' => false,
            ];

            $this->dispatch('scan-error');
        }

        // Keep only last 5 scans
        $this->recentScans = array_slice($this->recentScans, -5);

        $this->scanInput = '';
        $this->isScanning = false;
    }

    public function render()
    {
        return view('livewire.membership.simple-scanner');
    }
}
