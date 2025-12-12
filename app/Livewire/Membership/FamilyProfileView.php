<?php

namespace App\Livewire\Membership;

use App\Models\Membership\Member;
use Livewire\Component;

class FamilyProfileView extends Component
{
    public ?Member $member = null;

    public array $familyMembers = [];

    public bool $showProfile = false;

    public array $familyStatistics = [];

    public function loadFamilyProfile(int $memberId): void
    {
        $this->member = Member::with(['familyMembers', 'subscriptions.active', 'fees' => function ($query) {
            $query->where('status', 'pending')->orWhere('status', 'paid');
        }])->find($memberId);

        if ($this->member) {
            $this->familyMembers = $this->member->familyMembers->map(function ($fm) {
                return [
                    'id' => $fm->id,
                    'name' => $fm->full_name,
                    'relationship' => $fm->relationship,
                    'age' => $fm->age,
                    'gender' => $fm->gender,
                    'status' => $fm->status,
                    'photo_path' => $fm->photo_path,
                    'barcode_number' => $fm->barcode_number,
                    'date_of_birth' => $fm->date_of_birth?->format('M d, Y'),
                    'is_active' => $fm->isActive(),
                ];
            })->toArray();

            $this->calculateFamilyStatistics();
            $this->showProfile = true;
        }
    }

    public function closeProfile(): void
    {
        $this->showProfile = false;
        $this->member = null;
        $this->familyMembers = [];
        $this->familyStatistics = [];
    }

    protected function calculateFamilyStatistics(): void
    {
        if (! $this->member) {
            return;
        }

        $totalMembers = count($this->familyMembers) + 1; // +1 for primary member
        $activeMembers = collect($this->familyMembers)->filter(fn ($fm) => $fm['is_active'])->count() + ($this->member->isActive() ? 1 : 0);
        $childrenCount = collect($this->familyMembers)->filter(fn ($fm) => in_array($fm['relationship'], ['son', 'daughter']))->count();
        $adultsCount = $totalMembers - $childrenCount;

        $ages = collect($this->familyMembers)->pluck('age')->filter()->add($this->member->age)->filter();
        $averageAge = $ages->isNotEmpty() ? round($ages->avg(), 1) : 0;

        $pendingFees = $this->member->fees->where('status', 'pending')->sum('amount');
        $paidFees = $this->member->fees->where('status', 'paid')->sum('amount');

        $this->familyStatistics = [
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'children_count' => $childrenCount,
            'adults_count' => $adultsCount,
            'average_age' => $averageAge,
            'pending_fees' => $pendingFees,
            'paid_fees' => $paidFees,
            'membership_duration' => $this->member->join_date ? $this->member->join_date->diffInDays(now()) : 0,
            'days_until_expiry' => $this->member->expiry_date ? max(0, now()->diffInDays($this->member->expiry_date)) : 0,
        ];
    }

    public function getMemberAgeText(): string
    {
        if (! $this->member) {
            return '';
        }

        $age = $this->member->age;
        if (! $age) {
            return '';
        }

        return $age.' years old';
    }

    public function getMembershipStatus(): array
    {
        if (! $this->member) {
            return ['status' => 'unknown', 'color' => 'gray', 'icon' => 'question-mark-circle'];
        }

        if ($this->member->isActive()) {
            return ['status' => 'active', 'color' => 'green', 'icon' => 'check-circle'];
        } elseif ($this->member->isExpired()) {
            return ['status' => 'expired', 'color' => 'red', 'icon' => 'x-circle'];
        } elseif ($this->member->isExpiringSoon()) {
            return ['status' => 'expiring', 'color' => 'yellow', 'icon' => 'exclamation-triangle'];
        }

        return ['status' => 'inactive', 'color' => 'gray', 'icon' => 'pause-circle'];
    }

    public function getRelationshipIcon(string $relationship): string
    {
        return match (strtolower($relationship)) {
            'spouse', 'wife', 'husband' => 'heart',
            'son' => 'user',
            'daughter' => 'user',
            'father' => 'user',
            'mother' => 'user',
            'brother' => 'user',
            'sister' => 'user',
            default => 'user',
        };
    }

    public function getRelationshipColor(string $relationship): string
    {
        return match (strtolower($relationship)) {
            'spouse', 'wife', 'husband' => 'pink',
            'son' => 'blue',
            'daughter' => 'purple',
            'father' => 'gray',
            'mother' => 'gray',
            'brother' => 'green',
            'sister' => 'green',
            default => 'gray',
        };
    }

    public function render()
    {
        return view('livewire.membership.family-profile-view');
    }
}
