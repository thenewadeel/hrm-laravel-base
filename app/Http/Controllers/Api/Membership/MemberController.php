<?php

namespace App\Http\Controllers\Api\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\Member;
use App\Services\Membership\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}

    /**
     * Display a listing of members.
     */
    public function index(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $search = $request->get('search', '');
        $filters = $request->only(['status', 'has_family', 'subscription_status', 'per_page']);
        
        $members = $this->membershipService->searchMembers($organizationId, $search, $filters);
        
        // Apply pagination
        $perPage = $filters['per_page'] ?? 20;
        $paginatedMembers = $members->paginate($perPage);
        
        return response()->json([
            'data' => $paginatedMembers->items(),
            'pagination' => [
                'current_page' => $paginatedMembers->currentPage(),
                'last_page' => $paginatedMembers->lastPage(),
                'per_page' => $paginatedMembers->perPage(),
                'total' => $paginatedMembers->total(),
            ],
        ]);
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|unique:members,email',
            'phone' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date|before:today',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'join_date' => 'required|date|before_or_equal:today',
            'expiry_date' => 'nullable|date|after:join_date',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $organizationId = Auth::user()->current_organization_id;
        $memberData = array_merge($request->all(), [
            'organization_id' => $organizationId,
        ]);
        
        $member = $this->membershipService->createMember($memberData);
        
        return response()->json([
            'success' => true,
            'data' => $member,
            'message' => 'Member created successfully.',
        ], 201);
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member): JsonResponse
    {
        $this->authorize('view', $member);
        
        $member->load(['familyMembers', 'activeSubscription.subscriptionPlan', 'fees']);
        
        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    /**
     * Update the specified member in storage.
     */
    public function update(Request $request, Member $member): JsonResponse
    {
        $this->authorize('update', $member);
        
        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|unique:members,email,' . $member->id,
            'phone' => 'sometimes|string|max:50',
            'date_of_birth' => 'sometimes|date|before:today',
            'gender' => 'sometimes|in:male,female,other',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'expiry_date' => 'sometimes|date',
            'notes' => 'sometimes|string|max:1000',
        ]);
        
        $member = $this->membershipService->updateMember($member, $request->all());
        
        return response()->json([
            'success' => true,
            'data' => $member,
            'message' => 'Member updated successfully.',
        ]);
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member): JsonResponse
    {
        $this->authorize('delete', $member);
        
        $member->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Member deleted successfully.',
        ]);
    }

    /**
     * Get member statistics.
     */
    public function statistics(): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $statistics = $this->membershipService->getMemberStatistics($organizationId);
        
        return response()->json([
            'success' => true,
            'data' => $statistics,
        ]);
    }

    /**
     * Get expiring members.
     */
    public function expiring(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $days = $request->get('days', 30);
        
        $members = $this->membershipService->getExpiringMembers($organizationId, $days);
        
        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Search members.
     */
    public function search(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $search = $request->get('q', '');
        $filters = $request->only(['status', 'has_family']);
        
        if (strlen($search) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Search term must be at least 2 characters.',
            ], 400);
        }
        
        $members = $this->membershipService->searchMembers($organizationId, $search, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $members->take(20), // Limit search results
        ]);
    }
}
