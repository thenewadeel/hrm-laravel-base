<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\ProcessFeePaymentRequest;
use App\Models\Membership\MemberFee;
use App\Models\Membership\Member;
use App\Services\Membership\FeeService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FeeController extends Controller
{
    public function __construct(
        private FeeService $feeService
    ) {}

    /**
     * Display a listing of fees.
     */
    public function index(Request $request): View
    {
        $organizationId = Auth::user()->current_organization_id;
        $filters = $request->only(['status', 'fee_type', 'start_date', 'end_date', 'member_search']);
        
        $query = MemberFee::where('organization_id', $organizationId)
            ->with('member');
        
        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['fee_type'])) {
            $query->where('fee_type', $filters['fee_type']);
        }
        
        if (isset($filters['start_date'])) {
            $query->where('due_date', '>=', $filters['start_date']);
        }
        
        if (isset($filters['end_date'])) {
            $query->where('due_date', '<=', $filters['end_date']);
        }
        
        if (isset($filters['member_search'])) {
            $query->whereHas('member', function ($q) use ($filters) {
                $q->where('first_name', 'like', '%' . $filters['member_search'] . '%')
                  ->orWhere('last_name', 'like', '%' . $filters['member_search'] . '%')
                  ->orWhere('membership_number', 'like', '%' . $filters['member_search'] . '%');
            });
        }
        
        $fees = $query->orderBy('due_date', 'desc')->paginate(25);
        
        return view('membership.fees.index', compact('fees', 'filters'));
    }

    /**
     * Show the form for creating a new fee.
     */
    public function create(Request $request): View
    {
        $organizationId = Auth::user()->current_organization_id;
        $memberId = $request->get('member_id');
        
        $member = null;
        if ($memberId) {
            $member = Member::where('organization_id', $organizationId)
                ->findOrFail($memberId);
        }
        
        $members = Member::where('organization_id', $organizationId)
            ->active()
            ->orderBy('first_name')
            ->get();
        
        return view('membership.fees.create', compact('member', 'members'));
    }

    /**
     * Store a newly created fee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        
        $request->validate([
            'member_id' => 'required|exists:members,id,organization_id,' . $organizationId,
            'fee_type' => 'required|in:subscription,late_fee,penalty,additional_service',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'due_date' => 'required|date|after_or_equal:today',
            'distribute_to_accounts' => 'boolean',
        ]);
        
        $member = Member::where('organization_id', $organizationId)
            ->findOrFail($request->member_id);
        
        $feeData = array_merge($request->all(), [
            'organization_id' => $organizationId,
        ]);
        
        $fee = $this->feeService->createFee($member, $feeData);
        
        return redirect()
            ->route('fees.show', $fee)
            ->with('success', 'Fee created successfully.');
    }

    /**
     * Display the specified fee.
     */
    public function show(MemberFee $fee): View
    {
        $this->authorize('view', $fee);
        
        $fee->load('member');
        
        return view('membership.fees.show', compact('fee'));
    }

    /**
     * Show the form for editing the specified fee.
     */
    public function edit(MemberFee $fee): View
    {
        $this->authorize('update', $fee);
        
        return view('membership.fees.edit', compact('fee'));
    }

    /**
     * Update the specified fee in storage.
     */
    public function update(Request $request, MemberFee $fee): RedirectResponse
    {
        $this->authorize('update', $fee);
        
        $request->validate([
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'due_date' => 'required|date',
        ]);
        
        $fee->update($request->only(['description', 'amount', 'due_date']));
        
        return redirect()
            ->route('fees.show', $fee)
            ->with('success', 'Fee updated successfully.');
    }

    /**
     * Remove the specified fee from storage.
     */
    public function destroy(MemberFee $fee): RedirectResponse
    {
        $this->authorize('delete', $fee);
        
        $fee->delete();
        
        return redirect()
            ->route('fees.index')
            ->with('success', 'Fee deleted successfully.');
    }

    /**
     * Process fee payment.
     */
    public function processPayment(ProcessFeePaymentRequest $request, MemberFee $fee): RedirectResponse
    {
        $this->authorize('update', $fee);
        
        $paymentData = $request->validated();
        
        $this->feeService->processFeePayment($fee, $paymentData);
        
        return redirect()
            ->route('fees.show', $fee)
            ->with('success', 'Payment processed successfully.');
    }

    /**
     * Waive fee.
     */
    public function waive(Request $request, MemberFee $fee): RedirectResponse
    {
        $this->authorize('update', $fee);
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $this->feeService->waiveFee($fee, $request->reason);
        
        return redirect()
            ->route('fees.show', $fee)
            ->with('success', 'Fee waived successfully.');
    }

    /**
     * Generate overdue fees.
     */
    public function generateOverdueFees(): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $generatedCount = $this->feeService->generateOverdueFees($organizationId);
        
        return response()->json([
            'success' => true,
            'generated_count' => $generatedCount,
            'message' => "Generated {$generatedCount} overdue fees.",
        ]);
    }

    /**
     * Get fee statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $filters = $request->only(['start_date', 'end_date']);
        
        $statistics = $this->feeService->getFeeStatistics($organizationId, $filters);
        
        return response()->json($statistics);
    }

    /**
     * Get member fee summary.
     */
    public function memberSummary(Member $member): JsonResponse
    {
        $this->authorize('view', $member);
        
        $summary = $this->feeService->getMemberFeeSummary($member);
        
        return response()->json($summary);
    }
}
