<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use Livewire\Component;

class SimpleScanner extends Component
{
    public string $scanInput = '';

    public array $recentScans = [];

    public bool $isScanning = false;

    public ?array $currentMember = null;

    public bool $showProfile = false;

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
            $familyMembers = $member->familyMembers()->get()->map(function ($fm) {
                return [
                    'id' => $fm->id,
                    'name' => $fm->full_name,
                    'relationship' => $fm->relationship,
                    'age' => $fm->age,
                    'status' => $fm->status,
                    'photo_path' => $fm->photo_path,
                    'is_active' => $fm->isActive(),
                    'relationship_color' => $this->getRelationshipColor($fm->relationship),
                ];
            })->toArray();

            // Get recent activity (mock data for demo)
            $recentActivity = $this->generateRecentActivity($member);

            $this->currentMember = [
                'id' => $member->id,
                'name' => $member->full_name,
                'member_id' => $member->membership_number,
                'email' => $member->email,
                'phone' => $member->phone,
                'status' => $member->status,
                'join_date' => $member->join_date?->format('M d, Y'),
                'expiry_date' => $member->expiry_date?->format('M d, Y'),
                'age' => $member->age,
                'photo_path' => $member->photo_path,
                'family_members' => $familyMembers,
                'is_active' => $member->isActive(),
                'is_expired' => $member->isExpired(),
                'is_expiring_soon' => $member->isExpiringSoon(),
                'membership_type' => $this->getMembershipType($member),
                'access_level' => $this->getAccessLevel($member),
                'recent_activity' => $recentActivity,
                'check_in_count' => rand(15, 45), // Mock data for demo
                'last_visit' => now()->subDays(rand(1, 7))->format('M d, Y'),
            ];

            $this->showProfile = true;

            $this->recentScans[] = [
                'id' => $member->id,
                'name' => $member->full_name,
                'member_id' => $member->membership_number,
                'status' => $member->status,
                'time' => now()->format('H:i:s'),
                'success' => true,
            ];

            $this->dispatch('scan-success', member: $member->full_name);
        } else {
            $this->currentMember = null;
            $this->showProfile = false;

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

    public function scanDemo(string $type): void
    {
        if ($type === 'valid') {
            $this->scanInput = 'MEM001';
        } else {
            $this->scanInput = 'INVALID123';
        }
        $this->scan();
    }

    public function closeProfile(): void
    {
        $this->showProfile = false;
        $this->currentMember = null;
    }

    public function checkInMember(): void
    {
        if ($this->currentMember) {
            $this->dispatch('member-checked-in', member: $this->currentMember['name']);
            // In a real implementation, you would log this check-in to the database
        }
    }

    public function addNote(): void
    {
        $this->dispatch('add-note-modal', memberId: $this->currentMember['id']);
    }

    public function viewFullProfile(): void
    {
        $this->dispatch('view-full-profile', memberId: $this->currentMember['id']);
    }

    private function getRelationshipColor(string $relationship): string
    {
        return match (strtolower($relationship)) {
            'spouse', 'wife', 'husband' => 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-200',
            'son', 'daughter', 'child' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'father', 'mother', 'parent' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }

    private function getMembershipType(Member $member): string
    {
        // Mock membership types for demo
        $types = ['Gold', 'Platinum', 'Premium', 'Standard', 'Corporate'];

        return $types[array_rand($types)];
    }

    private function getAccessLevel(Member $member): string
    {
        // Mock access levels for demo
        $levels = ['Full Access', 'Premium Access', 'Standard Access', 'Limited Access'];

        return $levels[array_rand($levels)];
    }

    private function generateRecentActivity(Member $member): array
    {
        // Generate mock recent activity for demo
        $activities = [];
        $activities[] = [
            'type' => 'check_in',
            'description' => 'Checked in at Main Entrance',
            'time' => now()->subHours(2)->format('h:i A'),
            'date' => 'Today',
        ];

        if (rand(0, 1)) {
            $activities[] = [
                'type' => 'payment',
                'description' => 'Restaurant payment - $45.00',
                'time' => now()->subDays(1)->format('h:i A'),
                'date' => 'Yesterday',
            ];
        }

        if (rand(0, 1)) {
            $activities[] = [
                'type' => 'facility',
                'description' => 'Used Tennis Court #3',
                'time' => now()->subDays(3)->format('h:i A'),
                'date' => now()->subDays(3)->format('M d'),
            ];
        }

        return $activities;
    }

    public function render()
    {
        return view('livewire.membership.simple-scanner');
    }
}
