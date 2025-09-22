<?php

namespace App\Livewire\Accounting;

use Livewire\Component;

class Reports extends Component
{
    /**
     * The data for the Trial Balance report.
     * @var array
     */
    public $trialBalanceData;

    /**
     * The data for the Balance Sheet report.
     * @var array
     */
    public $balanceSheetData;

    /**
     * The data for the Income Statement report.
     * @var array
     */
    public $incomeStatementData;

    /**
     * Mount the component and populate with initial data.
     * In a real application, you would fetch this data from a service or the database.
     *
     * @return void
     */
    public function mount()
    {
        // Dummy data for the Trial Balance
        $this->trialBalanceData = [
            ['account' => 'Cash', 'debit' => 10000, 'credit' => 0],
            ['account' => 'Accounts Receivable', 'debit' => 5000, 'credit' => 0],
            ['account' => 'Prepaid Insurance', 'debit' => 2000, 'credit' => 0],
            ['account' => 'Equipment', 'debit' => 15000, 'credit' => 0],
            ['account' => 'Accounts Payable', 'debit' => 0, 'credit' => 6000],
            ['account' => 'Unearned Revenue', 'debit' => 0, 'credit' => 3000],
            ['account' => 'Common Stock', 'debit' => 0, 'credit' => 18000],
            ['account' => 'Retained Earnings', 'debit' => 0, 'credit' => 5000],
            ['account' => 'Service Revenue', 'debit' => 0, 'credit' => 10000],
            ['account' => 'Rent Expense', 'debit' => 5000, 'credit' => 0],
            ['account' => 'Salaries Expense', 'debit' => 4000, 'credit' => 0],
        ];

        // Dummy data for the Balance Sheet
        $this->balanceSheetData = [
            'assets' => [
                ['name' => 'Current Assets', 'amount' => 17000],
                ['name' => 'Fixed Assets', 'amount' => 15000],
                ['name' => 'Total Assets', 'amount' => 32000],
            ],
            'liabilities' => [
                ['name' => 'Current Liabilities', 'amount' => 9000],
                ['name' => 'Total Liabilities', 'amount' => 9000],
            ],
            'equity' => [
                ['name' => 'Retained Earnings', 'amount' => 5000],
                ['name' => 'Common Stock', 'amount' => 18000],
                ['name' => 'Total Equity', 'amount' => 23000],
            ],
            'total_liabilities_equity' => 32000,
        ];

        // Dummy data for the Income Statement
        $this->incomeStatementData = [
            'revenue' => [
                ['name' => 'Service Revenue', 'amount' => 10000],
                ['name' => 'Total Revenue', 'amount' => 10000],
            ],
            'expenses' => [
                ['name' => 'Rent Expense', 'amount' => 5000],
                ['name' => 'Salaries Expense', 'amount' => 4000],
                ['name' => 'Total Expenses', 'amount' => 9000],
            ],
            'net_income' => 1000,
        ];
    }

    /**
     * Render the component's view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.accounting.reports', [
            'trialBalanceData' => $this->trialBalanceData,
            'balanceSheetData' => $this->balanceSheetData,
            'incomeStatementData' => $this->incomeStatementData,
        ]);
    }
}
