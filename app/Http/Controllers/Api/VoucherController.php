<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateExpenseVoucherRequest;
use App\Http\Requests\CreatePurchaseVoucherRequest;
use App\Http\Requests\CreateSalaryVoucherRequest;
use App\Http\Requests\CreateSalesVoucherRequest;
use App\Models\Accounting\JournalEntry;
use App\Services\ExpenseVoucherService;
use App\Services\PurchaseVoucherService;
use App\Services\SalaryVoucherService;
use App\Services\SalesVoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function __construct(
        private SalesVoucherService $salesVoucherService,
        private PurchaseVoucherService $purchaseVoucherService,
        private SalaryVoucherService $salaryVoucherService,
        private ExpenseVoucherService $expenseVoucherService
    ) {}

    /**
     * Display a listing of vouchers.
     */
    public function index(Request $request): JsonResponse
    {
        $vouchers = JournalEntry::with(['customer', 'vendor', 'createdBy'])
            ->where('organization_id', auth()->user()->current_organization_id)
            ->when($request->voucher_type, function ($query, $voucherType) {
                $query->where('voucher_type', $voucherType);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json($vouchers);
    }

    /**
     * Create a sales voucher.
     */
    public function createSales(CreateSalesVoucherRequest $request): JsonResponse
    {
        $voucher = $this->salesVoucherService->createSalesVoucher($request->validated());

        return response()->json([
            'message' => 'Sales voucher created successfully',
            'voucher' => $voucher->load(['customer', 'ledgerEntries.account']),
        ], 201);
    }

    /**
     * Create a purchase voucher.
     */
    public function createPurchase(CreatePurchaseVoucherRequest $request): JsonResponse
    {
        $voucher = $this->purchaseVoucherService->createPurchaseVoucher($request->validated());

        return response()->json([
            'message' => 'Purchase voucher created successfully',
            'voucher' => $voucher->load(['vendor', 'ledgerEntries.account']),
        ], 201);
    }

    /**
     * Create a salary voucher.
     */
    public function createSalary(CreateSalaryVoucherRequest $request): JsonResponse
    {
        $voucher = $this->salaryVoucherService->createSalaryVoucher($request->validated());

        return response()->json([
            'message' => 'Salary voucher created successfully',
            'voucher' => $voucher->load(['ledgerEntries.account']),
        ], 201);
    }

    /**
     * Create an expense voucher.
     */
    public function createExpense(CreateExpenseVoucherRequest $request): JsonResponse
    {
        $voucher = $this->expenseVoucherService->createExpenseVoucher($request->validated());

        return response()->json([
            'message' => 'Expense voucher created successfully',
            'voucher' => $voucher->load(['ledgerEntries.account']),
        ], 201);
    }

    /**
     * Display specified voucher.
     */
    public function show(JournalEntry $voucher): JsonResponse
    {
        $this->authorize('view', $voucher);

        return response()->json(
            $voucher->load(['customer', 'vendor', 'createdBy', 'ledgerEntries.account'])
        );
    }

    /**
     * Post a voucher.
     */
    public function post(JournalEntry $voucher): JsonResponse
    {
        $this->authorize('update', $voucher);

        if ($voucher->status !== 'draft') {
            return response()->json(['message' => 'Only draft vouchers can be posted'], 422);
        }

        $voucher->update([
            'status' => 'posted',
            'posted_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Voucher posted successfully',
            'voucher' => $voucher,
        ]);
    }

    /**
     * Void a posted voucher.
     */
    public function void(JournalEntry $voucher): JsonResponse
    {
        $this->authorize('delete', $voucher);

        try {
            $voucher->void();

            return response()->json([
                'message' => 'Voucher voided successfully',
                'voucher' => $voucher,
            ]);
        } catch (\LogicException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
