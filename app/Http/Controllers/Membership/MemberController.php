<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Http\Requests\Membership\StoreMemberRequest;
use App\Http\Requests\Membership\UpdateMemberRequest;
use App\Http\Requests\Membership\StoreFamilyMemberRequest;
use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use App\Services\Membership\MembershipService;
use App\Services\Membership\CardPrintingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MemberController extends Controller
{
    public function __construct(
        private MembershipService $membershipService,
        private CardPrintingService $cardPrintingService
    ) {}

    /**
     * Display a listing of members.
     */
    public function index(Request $request): View
    {
        $organizationId = Auth::user()->current_organization_id;
        $search = $request->get('search', '');
        $filters = $request->only(['status', 'has_family', 'subscription_status']);
        
        $members = $this->membershipService->searchMembers($organizationId, $search, $filters);
        
        return view('membership.members.index', compact('members', 'search', 'filters'));
    }

    /**
     * Show the form for creating a new member.
     */
    public function create(): View
    {
        return view('membership.members.create');
    }

    /**
     * Store a newly created member in storage.
     */
    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $memberData = array_merge($request->validated(), [
            'organization_id' => $organizationId,
        ]);
        
        $member = $this->membershipService->createMember($memberData);
        
        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Member created successfully.');
    }

    /**
     * Display the specified member.
     */
    public function show(Member $member): View
    {
        $this->authorize('view', $member);
        
        $member->load(['familyMembers', 'subscriptions.subscriptionPlan', 'fees']);
        
        return view('membership.members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified member.
     */
    public function edit(Member $member): View
    {
        $this->authorize('update', $member);
        
        return view('membership.members.edit', compact('member'));
    }

    /**
     * Update the specified member in storage.
     */
    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);
        
        $member = $this->membershipService->updateMember($member, $request->validated());
        
        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Remove the specified member from storage.
     */
    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);
        
        $member->delete();
        
        return redirect()
            ->route('members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Add a family member to the specified member.
     */
    public function addFamilyMember(StoreFamilyMemberRequest $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);
        
        $familyMember = $this->membershipService->addFamilyMember($member, $request->validated());
        
        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Family member added successfully.');
    }

    /**
     * Deactivate the specified member.
     */
    public function deactivate(Member $member): RedirectResponse
    {
        $this->authorize('update', $member);
        
        $this->membershipService->deactivateMember($member);
        
        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Member deactivated successfully.');
    }

    /**
     * Suspend the specified member.
     */
    public function suspend(Request $request, Member $member): RedirectResponse
    {
        $this->authorize('update', $member);
        
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);
        
        $this->membershipService->suspendMember($member, $request->reason);
        
        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Member suspended successfully.');
    }

    /**
     * Reactivate the specified member.
     */
    public function reactivate(Member $member): RedirectResponse
    {
        $this->authorize('update', $member);
        
        $this->membershipService->reactivateMember($member);
        
        return redirect()
            ->route('members.show', $member)
            ->with('success', 'Member reactivated successfully.');
    }

    /**
     * Generate and download member card.
     */
    public function printCard(Request $request, Member $member): JsonResponse
    {
        $this->authorize('view', $member);
        
        $template = $request->get('template', 'default');
        
        if (!$this->cardPrintingService->validateTemplate($template)) {
            return response()->json(['error' => 'Invalid template'], 400);
        }
        
        $cardHtml = $this->cardPrintingService->generateMemberCard($member, $template);
        $pdfPath = $this->cardPrintingService->exportCardsToPdf($cardHtml);
        
        return response()->json([
            'success' => true,
            'pdf_url' => route('members.downloadCard', ['path' => $pdfPath]),
        ]);
    }

    /**
     * Download member card PDF.
     */
    public function downloadCard(string $path)
    {
        if (!Storage::exists($path)) {
            abort(404);
        }
        
        return Storage::download($path);
    }

    /**
     * Get member statistics.
     */
    public function statistics(): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $statistics = $this->membershipService->getMemberStatistics($organizationId);
        
        return response()->json($statistics);
    }

    /**
     * Get expiring members.
     */
    public function expiring(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $days = $request->get('days', 30);
        
        $members = $this->membershipService->getExpiringMembers($organizationId, $days);
        
        return response()->json($members);
    }
}
