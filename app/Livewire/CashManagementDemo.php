<?php

namespace App\Livewire;

use App\Models\Accounting\CashPayment;
use App\Models\Accounting\CashReceipt;
use App\Models\Accounting\ChartOfAccount;
use App\Services\CashPaymentService;
use App\Services\CashReceiptService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CashManagementDemo extends Component
{
    public int $organizationId;

    public string $mode = 'receipt';

    // Cash Receipt data
    public array $receiptData = [
        'received_from' => '',
        'amount' => '',
        'cash_account_id' => '',
        'credit_account_id' => '',
        'date' => '',
        'description' => '',
        'notes' => '',
    ];

    // Cash Payment data
    public array $paymentData = [
        'paid_to' => '',
        'amount' => '',
        'cash_account_id' => '',
        'debit_account_id' => '',
        'date' => '',
        'purpose' => '',
        'notes' => '',
    ];

    public $cashAccounts = [];

    public $revenueAccounts = [];

    public $expenseAccounts = [];

    public $recentReceipts = null;

    public $recentPayments = null;

    public function mount(int $organizationId)
    {
        $this->organizationId = $organizationId;
        $this->receiptData['date'] = now()->format('Y-m-d');
        $this->paymentData['date'] = now()->format('Y-m-d');
        $this->loadAccounts();
        $this->loadRecentTransactions();
    }

    public function loadAccounts()
    {
        $this->cashAccounts = ChartOfAccount::where('organization_id', $this->organizationId)
            ->where('type', 'asset')
            ->get();

        $this->revenueAccounts = ChartOfAccount::where('organization_id', $this->organizationId)
            ->whereIn('type', ['revenue', 'liability', 'equity'])
            ->get();

        $this->expenseAccounts = ChartOfAccount::where('organization_id', $this->organizationId)
            ->whereIn('type', ['expense', 'asset'])
            ->get();
    }

    public function loadRecentTransactions()
    {
        $this->recentReceipts = CashReceipt::where('organization_id', $this->organizationId)
            ->with(['cashAccount', 'creditAccount'])
            ->latest()
            ->take(5)
            ->get();

        $this->recentPayments = CashPayment::where('organization_id', $this->organizationId)
            ->with(['cashAccount', 'debitAccount'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function setMode(string $mode)
    {
        $this->mode = $mode;
    }

    public function createReceipt()
    {
        $this->validate([
            'receiptData.received_from' => 'required|string|max:255',
            'receiptData.amount' => 'required|numeric|min:0.01',
            'receiptData.cash_account_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) {
                    $query->where('organization_id', $this->organizationId);
                }),
            ],
            'receiptData.credit_account_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) {
                    $query->where('organization_id', $this->organizationId);
                }),
            ],
            'receiptData.date' => 'required|date',
            'receiptData.description' => 'nullable|string|max:500',
            'receiptData.notes' => 'nullable|string|max:1000',
        ]);

        $service = app(CashReceiptService::class);
        $receipt = $service->createReceipt($this->receiptData, $this->organizationId);

        $this->resetReceiptForm();
        $this->loadRecentTransactions();

        $this->dispatch('cash-receipt-created', receiptId: $receipt->id);
        session()->flash('receipt_success', 'Cash receipt created successfully!');
    }

    public function createPayment()
    {
        $this->validate([
            'paymentData.paid_to' => 'required|string|max:255',
            'paymentData.amount' => 'required|numeric|min:0.01',
            'paymentData.cash_account_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) {
                    $query->where('organization_id', $this->organizationId);
                }),
            ],
            'paymentData.debit_account_id' => [
                'required',
                Rule::exists('chart_of_accounts', 'id')->where(function ($query) {
                    $query->where('organization_id', $this->organizationId);
                }),
            ],
            'paymentData.date' => 'required|date',
            'paymentData.purpose' => 'nullable|string|max:500',
            'paymentData.notes' => 'nullable|string|max:1000',
        ]);

        $service = app(CashPaymentService::class);
        $payment = $service->createPayment($this->paymentData, $this->organizationId);

        $this->resetPaymentForm();
        $this->loadRecentTransactions();

        $this->dispatch('cash-payment-created', paymentId: $payment->id);
        session()->flash('payment_success', 'Cash payment created successfully!');
    }

    private function resetReceiptForm()
    {
        $this->receiptData = [
            'received_from' => '',
            'amount' => '',
            'cash_account_id' => '',
            'credit_account_id' => '',
            'date' => now()->format('Y-m-d'),
            'description' => '',
            'notes' => '',
        ];
    }

    private function resetPaymentForm()
    {
        $this->paymentData = [
            'paid_to' => '',
            'amount' => '',
            'cash_account_id' => '',
            'debit_account_id' => '',
            'date' => now()->format('Y-m-d'),
            'purpose' => '',
            'notes' => '',
        ];
    }

    public function render()
    {
        return view('livewire.cash-management-demo');
    }
}
