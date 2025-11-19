<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OutstandingStatementsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutstandingStatementsController extends Controller
{
    public function __construct(
        private OutstandingStatementsService $outstandingService
    ) {}

    /**
     * Get accounts receivable aging report
     */
    public function receivablesAging(Request $request): JsonResponse
    {
        $customerId = $request->get('customer_id');
        
        $aging = $this->outstandingService->generateReceivablesAging($customerId);
        $summary = $this->outstandingService->getReceivablesAgingSummary();

        return response()->json([
            'aging_report' => $aging,
            'summary' => $summary,
        ]);
    }

    /**
     * Get accounts payable aging report
     */
    public function payablesAging(Request $request): JsonResponse
    {
        $vendorId = $request->get('vendor_id');
        
        $aging = $this->outstandingService->generatePayablesAging($vendorId);
        $summary = $this->outstandingService->getPayablesAgingSummary();

        return response()->json([
            'aging_report' => $aging,
            'summary' => $summary,
        ]);
    }

    /**
     * Get customer outstanding summary
     */
    public function customerSummary(): JsonResponse
    {
        $summary = $this->outstandingService->getCustomerOutstandingSummary();

        return response()->json($summary);
    }

    /**
     * Get vendor outstanding summary
     */
    public function vendorSummary(): JsonResponse
    {
        $summary = $this->outstandingService->getVendorOutstandingSummary();

        return response()->json($summary);
    }
}
