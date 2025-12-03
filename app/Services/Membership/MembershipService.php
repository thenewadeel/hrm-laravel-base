<?php

namespace App\Services\Membership;

use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use App\Models\Organization;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    /**
     * Create a new member with auto-generated membership and barcode numbers
     */
    public function createMember(array $data): Member
    {
        return DB::transaction(function () use ($data) {
            $data['membership_number'] = $this->generateMembershipNumber($data['organization_id']);
            $data['barcode_number'] = $this->generateBarcodeNumber($data['organization_id']);
            
            return Member::create($data);
        });
    }

    /**
     * Update an existing member
     */
    public function updateMember(Member $member, array $data): Member
    {
        return DB::transaction(function () use ($member, $data) {
            $member->update($data);
            return $member->fresh();
        });
    }

    /**
     * Add a family member to an existing member
     */
    public function addFamilyMember(Member $member, array $familyData): FamilyMember
    {
        return DB::transaction(function () use ($member, $familyData) {
            $familyData['organization_id'] = $member->organization_id;
            $familyData['primary_member_id'] = $member->id;
            $familyData['barcode_number'] = $this->generateFamilyBarcodeNumber($member->organization_id);
            
            return FamilyMember::create($familyData);
        });
    }

    /**
     * Deactivate a member and all family members
     */
    public function deactivateMember(Member $member): bool
    {
        return DB::transaction(function () use ($member) {
            // Deactivate family members first
            $member->familyMembers()->update(['status' => 'inactive']);
            
            // Deactivate the primary member
            $member->update(['status' => 'inactive']);
            
            return true;
        });
    }

    /**
     * Suspend a member (temporary deactivation)
     */
    public function suspendMember(Member $member, string $reason = null): bool
    {
        return DB::transaction(function () use ($member, $reason) {
            // Suspend family members first
            $member->familyMembers()->update(['status' => 'suspended']);
            
            // Suspend the primary member
            $member->update([
                'status' => 'suspended',
                'notes' => $member->notes . "\n\nSuspended: " . ($reason ?? 'No reason provided') . " - " . now()->toDateTimeString()
            ]);
            
            return true;
        });
    }

    /**
     * Reactivate a suspended member
     */
    public function reactivateMember(Member $member): bool
    {
        return DB::transaction(function () use ($member) {
            // Only reactivate if not expired
            if ($member->expiry_date && $member->expiry_date->isPast()) {
                throw new \InvalidArgumentException('Cannot reactivate expired member. Please renew subscription first.');
            }
            
            // Reactivate family members if they were suspended
            $member->familyMembers()
                ->where('status', 'suspended')
                ->update(['status' => 'active']);
            
            // Reactivate the primary member
            $member->update(['status' => 'active']);
            
            return true;
        });
    }

    /**
     * Generate unique membership number
     */
    public function generateMembershipNumber(int $organizationId): string
    {
        $prefix = 'MEM';
        $year = now()->format('Y');
        
        do {
            $sequence = str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT);
            $membershipNumber = "{$prefix}-{$year}-{$sequence}";
        } while (Member::where('organization_id', $organizationId)
            ->where('membership_number', $membershipNumber)
            ->exists());
        
        return $membershipNumber;
    }

    /**
     * Generate unique barcode number for members
     */
    public function generateBarcodeNumber(int $organizationId): string
    {
        $prefix = 'MBR';
        
        do {
            $sequence = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $barcodeNumber = "{$prefix}-{$organizationId}-{$sequence}";
        } while (Member::where('organization_id', $organizationId)
            ->where('barcode_number', $barcodeNumber)
            ->exists());
        
        return $barcodeNumber;
    }

    /**
     * Generate unique barcode number for family members
     */
    public function generateFamilyBarcodeNumber(int $organizationId): string
    {
        $prefix = 'FAM';
        
        do {
            $sequence = str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);
            $barcodeNumber = "{$prefix}-{$organizationId}-{$sequence}";
        } while (FamilyMember::where('organization_id', $organizationId)
            ->where('barcode_number', $barcodeNumber)
            ->exists());
        
        return $barcodeNumber;
    }

    /**
     * Search members across multiple fields
     */
    public function searchMembers(int $organizationId, string $search, array $filters = [])
    {
        $query = Member::where('organization_id', $organizationId)
            ->search($search);

        // Apply filters
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['has_family'])) {
            if ($filters['has_family']) {
                $query->has('familyMembers');
            } else {
                $query->doesntHave('familyMembers');
            }
        }

        if (isset($filters['subscription_status'])) {
            $query->whereHas('subscriptions', function ($q) use ($filters) {
                $q->where('status', $filters['subscription_status']);
            });
        }

        return $query->with(['familyMembers', 'activeSubscription'])->get();
    }

    /**
     * Get members expiring within specified days
     */
    public function getExpiringMembers(int $organizationId, int $days = 30)
    {
        return Member::where('organization_id', $organizationId)
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->where('status', 'active')
            ->with(['familyMembers', 'activeSubscription'])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Get expired members
     */
    public function getExpiredMembers(int $organizationId)
    {
        return Member::where('organization_id', $organizationId)
            ->where('expiry_date', '<', now())
            ->where('status', 'active')
            ->with(['familyMembers', 'activeSubscription'])
            ->orderBy('expiry_date')
            ->get();
    }

    /**
     * Update member status based on expiry date
     */
    public function updateExpiredMemberStatus(int $organizationId): int
    {
        return Member::where('organization_id', $organizationId)
            ->where('expiry_date', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);
    }

    /**
     * Get member statistics for an organization
     */
    public function getMemberStatistics(int $organizationId): array
    {
        $total = Member::where('organization_id', $organizationId)->count();
        $active = Member::where('organization_id', $organizationId)->active()->count();
        $inactive = Member::where('organization_id', $organizationId)->byStatus('inactive')->count();
        $suspended = Member::where('organization_id', $organizationId)->byStatus('suspended')->count();
        $expired = Member::where('organization_id', $organizationId)->expired()->count();
        
        $expiringNext30Days = Member::where('organization_id', $organizationId)
            ->where('expiry_date', '<=', now()->addDays(30))
            ->where('expiry_date', '>', now())
            ->where('status', 'active')
            ->count();

        $withFamilyMembers = Member::where('organization_id', $organizationId)
            ->has('familyMembers')
            ->count();

        return [
            'total_members' => $total,
            'active_members' => $active,
            'inactive_members' => $inactive,
            'suspended_members' => $suspended,
            'expired_members' => $expired,
            'expiring_next_30_days' => $expiringNext30Days,
            'members_with_family' => $withFamilyMembers,
            'activation_rate' => $total > 0 ? round(($active / $total) * 100, 2) : 0,
        ];
    }
}