<?php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Services\AccountingReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FinancialReportsController extends Controller
{
    protected $reportService;

    public function __construct(AccountingReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Generate Trial Balance report
     */
    public function trialBalance(Request $request): JsonResponse
    {
        $request->validate([
            'as_of_date' => 'nullable|date',
        ]);

        $asOfDate = $request->input('as_of_date') ?: now();

        try {
            $trialBalance = $this->reportService->generateTrialBalance($asOfDate);

            return response()->json([
                'data' => $trialBalance,
                'message' => 'Trial balance generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate trial balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Balance Sheet report
     */
    public function balanceSheet(Request $request): JsonResponse
    {
        $request->validate([
            'as_of_date' => 'nullable|date',
        ]);

        $asOfDate = $request->input('as_of_date') ?: now();

        try {
            $balanceSheet = $this->reportService->generateBalanceSheet($asOfDate);

            return response()->json([
                'data' => $balanceSheet,
                'message' => 'Balance sheet generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate balance sheet: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate Income Statement (Profit & Loss) report
     */
    public function incomeStatement(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $incomeStatement = $this->reportService->generateIncomeStatement(
                (new \DateTime($request->input('start_date'))),
                (new \DateTime($request->input('end_date')))
            );

            return response()->json([
                'data' => $incomeStatement,
                'message' => 'Income statement generated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate income statement: ' . $e->getMessage()
            ], 500);
        }
    }

    // The following methods can be removed since we don't need CRUD for reports
    // public function index() {}
    // public function store(Request $request) {}
    // public function show(string $id) {}
    // public function update(Request $request, string $id) {}
    // public function destroy(string $id) {}
}
