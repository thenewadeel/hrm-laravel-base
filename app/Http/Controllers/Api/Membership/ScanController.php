<?php

namespace App\Http\Controllers\Api\Membership;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\ScanBarcodeRequest;
use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ScanController extends Controller
{
    /**
     * Scan member barcode.
     */
    public function scanMember(ScanBarcodeRequest $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $barcodeNumber = $request->validated()['barcode'];
        
        // Search for member
        $member = Member::where('organization_id', $organizationId)
            ->where('barcode_number', $barcodeNumber)
            ->with(['familyMembers', 'activeSubscription.subscriptionPlan', 'fees'])
            ->first();
        
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found.',
            ], 404);
        }
        
        // Check if member is active
        if (!$member->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Member is not active.',
                'status' => $member->status,
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'type' => 'member',
            'data' => [
                'id' => $member->id,
                'membership_number' => $member->membership_number,
                'barcode_number' => $member->barcode_number,
                'full_name' => $member->full_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'status' => $member->status,
                'join_date' => $member->join_date,
                'expiry_date' => $member->expiry_date,
                'photo_url' => $member->photo_path ? url('storage/' . $member->photo_path) : null,
                'family_members_count' => $member->familyMembers->count(),
                'active_subscription' => $member->activeSubscription ? [
                    'plan_name' => $member->activeSubscription->subscriptionPlan->name,
                    'status' => $member->activeSubscription->status,
                    'end_date' => $member->activeSubscription->end_date,
                ] : null,
                'unpaid_fees_count' => $member->fees->where('status', 'pending')->count(),
            ],
        ]);
    }

    /**
     * Scan family member barcode.
     */
    public function scanFamilyMember(ScanBarcodeRequest $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $barcodeNumber = $request->validated()['barcode'];
        
        // Search for family member
        $familyMember = FamilyMember::where('organization_id', $organizationId)
            ->where('barcode_number', $barcodeNumber)
            ->with('primaryMember')
            ->first();
        
        if (!$familyMember) {
            return response()->json([
                'success' => false,
                'message' => 'Family member not found.',
            ], 404);
        }
        
        // Check if family member is active
        if (!$familyMember->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Family member is not active.',
                'status' => $familyMember->status,
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'type' => 'family_member',
            'data' => [
                'id' => $familyMember->id,
                'barcode_number' => $familyMember->barcode_number,
                'full_name' => $familyMember->full_name,
                'relationship' => $familyMember->relationship,
                'status' => $familyMember->status,
                'primary_member' => [
                    'id' => $familyMember->primaryMember->id,
                    'membership_number' => $familyMember->primaryMember->membership_number,
                    'full_name' => $familyMember->primaryMember->full_name,
                    'status' => $familyMember->primaryMember->status,
                    'expiry_date' => $familyMember->primaryMember->expiry_date,
                ],
                'photo_url' => $familyMember->photo_path ? url('storage/' . $familyMember->photo_path) : null,
            ],
        ]);
    }

    /**
     * Generic scan endpoint that determines member type.
     */
    public function scan(ScanBarcodeRequest $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $barcodeNumber = $request->validated()['barcode'];
        
        // Try member first
        $member = Member::where('organization_id', $organizationId)
            ->where('barcode_number', $barcodeNumber)
            ->first();
        
        if ($member) {
            return $this->scanMember($request);
        }
        
        // Try family member
        $familyMember = FamilyMember::where('organization_id', $organizationId)
            ->where('barcode_number', $barcodeNumber)
            ->first();
        
        if ($familyMember) {
            return $this->scanFamilyMember($request);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'No member or family member found with this barcode.',
        ], 404);
    }

    /**
     * Validate barcode format.
     */
    public function validateBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string|max:100',
        ]);
        
        $barcode = $request->barcode;
        
        // Check barcode format
        $memberPattern = '/^MBR-\d+-\d+$/';
        $familyPattern = '/^FAM-\d+-\d+$/';
        
        $isValid = preg_match($memberPattern, $barcode) || preg_match($familyPattern, $barcode);
        
        return response()->json([
            'valid' => $isValid,
            'type' => preg_match($memberPattern, $barcode) ? 'member' : (preg_match($familyPattern, $barcode) ? 'family_member' : 'unknown'),
            'barcode' => $barcode,
        ]);
    }

    /**
     * Get scan history.
     */
    public function scanHistory(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $limit = $request->get('limit', 50);
        
        // This would typically come from a scan logs table
        // For now, return recent members as a placeholder
        $recentMembers = Member::where('organization_id', $organizationId)
            ->orderBy('updated_at', 'desc')
            ->take($limit)
            ->get(['id', 'full_name', 'membership_number', 'barcode_number', 'updated_at']);
        
        return response()->json([
            'success' => true,
            'data' => $recentMembers,
        ]);
    }
}
