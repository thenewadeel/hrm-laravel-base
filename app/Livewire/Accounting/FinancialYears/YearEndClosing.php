<?php

namespace App\Livewire\Accounting\FinancialYears;

use App\Models\Accounting\FinancialYear;
use App\Services\FinancialYearService;
use Livewire\Component;

class YearEndClosing extends Component
{
    public FinancialYear $financialYear;

    public $confirmClose = false;

    public $closingSummary = null;

    public $newFinancialYearId = null;

    public $carryForward = false;

    public function mount(FinancialYear $financialYear)
    {
        $this->financialYear = $financialYear;
    }

    public function previewClosing()
    {
        $financialYearService = app(FinancialYearService::class);
        $trialBalance = $financialYearService->getFinancialYearTrialBalance($this->financialYear);

        // Calculate summary
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        foreach ($trialBalance as $account) {
            $accountType = $account['account']->type;
            $balance = abs($account['closing_balance']);

            switch ($accountType) {
                case 'revenue':
                    $totalRevenue += $balance;
                    break;
                case 'expense':
                    $totalExpenses += $balance;
                    break;
                case 'asset':
                    $totalAssets += $balance;
                    break;
                case 'liability':
                    $totalLiabilities += $balance;
                    break;
                case 'equity':
                    $totalEquity += $balance;
                    break;
            }
        }

        $netIncome = $totalRevenue - $totalExpenses;

        $this->closingSummary = [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_income' => $netIncome,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'trial_balance' => $trialBalance,
        ];
    }

    public function closeFinancialYear()
    {
        if (! $this->confirmClose) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please confirm the year-end closing.',
            ]);

            return;
        }

        try {
            $financialYearService = app(FinancialYearService::class);
            $result = $financialYearService->closeFinancialYear($this->financialYear);

            // Carry forward balances if requested
            if ($this->carryForward && $this->newFinancialYearId) {
                $newFinancialYear = FinancialYear::find($this->newFinancialYearId);
                if ($newFinancialYear) {
                    $financialYearService->carryForwardBalances($this->financialYear, $newFinancialYear);
                }
            }

            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => 'Financial year closed successfully.',
            ]);

            return redirect()->route('accounting.financial-years.index');
        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Error closing financial year: '.$e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        $availableFinancialYears = FinancialYear::where('organization_id', auth()->user()->current_organization_id)
            ->where('id', '!=', $this->financialYear->id)
            ->where('status', '!=', 'closed')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('livewire.accounting.financial-years.year-end-closing', [
            'availableFinancialYears' => $availableFinancialYears,
        ]);
    }
}
