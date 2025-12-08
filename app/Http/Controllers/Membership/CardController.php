<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\FamilyMember;
use App\Models\Membership\Member;
use App\Services\Membership\CardPrintingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CardController extends Controller
{
    public function __construct(
        private CardPrintingService $cardPrintingService
    ) {}

    /**
     * Display card printing interface.
     */
    public function index(): View
    {
        $organizationId = Auth::user()->current_organization_id;
        $statistics = $this->cardPrintingService->getCardStatistics($organizationId);
        $templates = $this->cardPrintingService->getAvailableTemplates();
        $settings = $this->cardPrintingService->getPrintingSettings();

        return view('membership.cards.index', compact('statistics', 'templates', 'settings'));
    }

    /**
     * Preview member card.
     */
    public function previewMember(Request $request, Member $member): JsonResponse
    {
        $this->authorize('view', $member);

        $template = $request->get('template', 'default');

        if (! $this->cardPrintingService->validateTemplate($template)) {
            return response()->json(['error' => 'Invalid template'], 400);
        }

        $preview = $this->cardPrintingService->previewCard($member, $template);

        return response()->json($preview);
    }

    /**
     * Preview family member card.
     */
    public function previewFamilyMember(Request $request, FamilyMember $familyMember): JsonResponse
    {
        $this->authorize('view', $familyMember->primaryMember);

        $template = $request->get('template', 'family');

        if (! $this->cardPrintingService->validateTemplate($template)) {
            return response()->json(['error' => 'Invalid template'], 400);
        }

        $data = [
            'family_member' => $familyMember,
            'primary_member' => $familyMember->primaryMember,
            'barcode' => $this->cardPrintingService->generateBarcode($familyMember->barcode_number),
            'organization' => $familyMember->organization,
            'photo_url' => $familyMember->photo_path ? Storage::url($familyMember->photo_path) : null,
        ];

        $html = view('membership.cards.templates.'.$template, $data)->render();

        return response()->json([
            'html' => $html,
            'template_info' => $this->cardPrintingService->getAvailableTemplates()[$template],
        ]);
    }

    /**
     * Generate and download member card PDF.
     */
    public function generateMemberCard(Request $request, Member $member): JsonResponse
    {
        $this->authorize('view', $member);

        $request->validate([
            'template' => 'required|string|in:'.implode(',', array_keys($this->cardPrintingService->getAvailableTemplates())),
        ]);

        $template = $request->template;
        $pdfPath = $this->cardPrintingService->generateSingleCardPdf($member, $template);

        return response()->json([
            'success' => true,
            'download_url' => route('cards.download', ['path' => $pdfPath]),
            'pdf_path' => $pdfPath,
        ]);
    }

    /**
     * Generate batch cards for multiple members.
     */
    public function generateBatchCards(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;

        $request->validate([
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'exists:members,id,organization_id,'.$organizationId,
            'template' => 'required|string|in:'.implode(',', array_keys($this->cardPrintingService->getAvailableTemplates())),
            'paper_size' => 'required|string|in:'.implode(',', array_keys($this->cardPrintingService->getPrintingSettings()['paper_sizes'])),
            'orientation' => 'required|string|in:landscape,portrait',
            'include_family' => 'boolean',
        ]);

        $pdfPath = $this->cardPrintingService->bulkGenerateCards($organizationId, $request->all());

        return response()->json([
            'success' => true,
            'download_url' => route('cards.download', ['path' => $pdfPath]),
            'pdf_path' => $pdfPath,
            'cards_generated' => count($request->member_ids),
        ]);
    }

    /**
     * Download generated card PDF.
     */
    public function download(string $path)
    {
        if (! Storage::exists($path)) {
            abort(404);
        }

        return Storage::download($path);
    }

    /**
     * Get card statistics.
     */
    public function statistics(): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $statistics = $this->cardPrintingService->getCardStatistics($organizationId);

        return response()->json($statistics);
    }

    /**
     * Get available templates.
     */
    public function templates(): JsonResponse
    {
        $templates = $this->cardPrintingService->getAvailableTemplates();

        return response()->json($templates);
    }

    /**
     * Get printing settings.
     */
    public function settings(): JsonResponse
    {
        $settings = $this->cardPrintingService->getPrintingSettings();

        return response()->json($settings);
    }

    /**
     * Display card scanning interface.
     */
    public function scanner(): View
    {
        return view('membership.scanner');
    }

    /**
     * Process barcode scan (API endpoint for external scanners).
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string|min:3',
            'location' => 'nullable|string|max:100',
            'device_id' => 'nullable|string|max:50',
        ]);

        $barcode = $request->barcode;
        $organizationId = Auth::user()->current_organization_id;

        try {
            // Try to find as member first
            $member = Member::where('barcode_number', $barcode)
                ->where('organization_id', $organizationId)
                ->with(['familyMembers', 'currentSubscription'])
                ->first();

            $result = [
                'success' => false,
                'access_granted' => false,
                'message' => '',
                'member_info' => null,
                'scan_time' => now()->toISOString(),
            ];

            if ($member) {
                $result['member_info'] = [
                    'id' => $member->id,
                    'name' => $member->full_name,
                    'membership_number' => $member->membership_number,
                    'status' => $member->status,
                    'photo_url' => $member->photo_path ? Storage::url($member->photo_path) : null,
                ];

                // Check access based on membership status and subscription
                if ($member->status === 'active' &&
                    $member->currentSubscription &&
                    $member->currentSubscription->status === 'active' &&
                    $member->currentSubscription->end_date >= now()) {
                    $result['success'] = true;
                    $result['access_granted'] = true;
                    $result['message'] = 'Access granted - Active member';
                } else {
                    $result['message'] = $this->getAccessDeniedMessage($member);
                }
            } else {
                // Try as family member
                $familyMember = FamilyMember::where('barcode_number', $barcode)
                    ->whereHas('primaryMember', function ($query) use ($organizationId) {
                        $query->where('organization_id', $organizationId);
                    })
                    ->with('primaryMember')
                    ->first();

                if ($familyMember) {
                    $primaryMember = $familyMember->primaryMember;
                    $result['member_info'] = [
                        'id' => $familyMember->id,
                        'name' => $familyMember->full_name,
                        'relationship' => $familyMember->relationship,
                        'primary_member' => $primaryMember->full_name,
                        'membership_number' => $primaryMember->membership_number,
                        'status' => $familyMember->status,
                        'photo_url' => $familyMember->photo_path ? Storage::url($familyMember->photo_path) : null,
                    ];

                    if ($familyMember->status === 'active' &&
                        $primaryMember->status === 'active' &&
                        $primaryMember->currentSubscription &&
                        $primaryMember->currentSubscription->status === 'active' &&
                        $primaryMember->currentSubscription->end_date >= now()) {
                        $result['success'] = true;
                        $result['access_granted'] = true;
                        $result['message'] = 'Access granted - Active family member';
                    } else {
                        $result['message'] = $this->getFamilyAccessDeniedMessage($familyMember, $primaryMember);
                    }
                } else {
                    $result['message'] = 'Member not found';
                }
            }

            // Log the access attempt
            $this->logAccessAttempt($result, $request);

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Scan failed: '.$e->getMessage(),
                'scan_time' => now()->toISOString(),
            ], 500);
        }
    }

    /**
     * Get access log for monitoring.
     */
    public function accessLog(Request $request): JsonResponse
    {
        $organizationId = Auth::user()->current_organization_id;
        $limit = min($request->get('limit', 50), 100);

        // In a real implementation, this would query a database table
        // For demo purposes, we'll return session data
        $logs = session('access_logs_'.$organizationId, []);

        return response()->json([
            'logs' => array_slice($logs, 0, $limit),
            'total' => count($logs),
        ]);
    }

    /**
     * Get appropriate access denied message for member.
     */
    private function getAccessDeniedMessage(Member $member): string
    {
        switch ($member->status) {
            case 'suspended':
                return 'Access denied - Membership suspended';
            case 'expired':
                return 'Access denied - Membership expired';
            case 'inactive':
                return 'Access denied - Membership inactive';
            default:
                if (! $member->currentSubscription) {
                    return 'Access denied - No active subscription';
                } elseif ($member->currentSubscription->status !== 'active') {
                    return 'Access denied - Subscription not active';
                } elseif ($member->currentSubscription->end_date < now()) {
                    return 'Access denied - Subscription expired';
                }

                return 'Access denied - Unknown reason';
        }
    }

    /**
     * Get appropriate access denied message for family member.
     */
    private function getFamilyAccessDeniedMessage(FamilyMember $familyMember, Member $primaryMember): string
    {
        if ($familyMember->status !== 'active') {
            return 'Access denied - Family member '.$familyMember->status;
        }

        return 'Access denied - '.$this->getAccessDeniedMessage($primaryMember);
    }

    /**
     * Log access attempt for audit trail.
     */
    private function logAccessAttempt(array $result, Request $request): void
    {
        $logEntry = [
            'barcode' => $request->barcode,
            'access_granted' => $result['access_granted'],
            'message' => $result['message'],
            'member_info' => $result['member_info'],
            'location' => $request->location,
            'device_id' => $request->device_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString(),
        ];

        $organizationId = Auth::user()->current_organization_id;
        $logs = session('access_logs_'.$organizationId, []);
        array_unshift($logs, $logEntry);
        $logs = array_slice($logs, 0, 100); // Keep only last 100 entries
        session(['access_logs_'.$organizationId => $logs]);
    }
}
