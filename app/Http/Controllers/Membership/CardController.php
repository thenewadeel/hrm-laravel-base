<?php

namespace App\Http\Controllers\Membership;

use App\Http\Controllers\Controller;
use App\Models\Membership\Member;
use App\Models\Membership\FamilyMember;
use App\Services\Membership\CardPrintingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        
        if (!$this->cardPrintingService->validateTemplate($template)) {
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
        
        if (!$this->cardPrintingService->validateTemplate($template)) {
            return response()->json(['error' => 'Invalid template'], 400);
        }
        
        $data = [
            'family_member' => $familyMember,
            'primary_member' => $familyMember->primaryMember,
            'barcode' => $this->cardPrintingService->generateBarcode($familyMember->barcode_number),
            'organization' => $familyMember->organization,
            'photo_url' => $familyMember->photo_path ? Storage::url($familyMember->photo_path) : null,
        ];
        
        $html = view('membership.cards.templates.' . $template, $data)->render();
        
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
            'template' => 'required|string|in:' . implode(',', array_keys($this->cardPrintingService->getAvailableTemplates())),
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
            'member_ids.*' => 'exists:members,id,organization_id,' . $organizationId,
            'template' => 'required|string|in:' . implode(',', array_keys($this->cardPrintingService->getAvailableTemplates())),
            'paper_size' => 'required|string|in:' . implode(',', array_keys($this->cardPrintingService->getPrintingSettings()['paper_sizes'])),
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
        if (!Storage::exists($path)) {
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
}
