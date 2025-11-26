<?php

namespace App\Livewire\Accounting\FinancialYears;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\FinancialYear;
use App\Services\FinancialYearService;
use Livewire\Component;

class OpeningBalanceForm extends Component
{
    public FinancialYear $financialYear;

    public $balances = [];

    public $account_type_filter = '';

    protected $rules = [
        'balances.*.debit_amount' => 'required|numeric|min:0',
        'balances.*.credit_amount' => 'required|numeric|min:0',
        'balances.*.description' => 'nullable|string|max:255',
    ];

    protected $messages = [
        'balances.*.debit_amount.required' => 'Debit amount is required.',
        'balances.*.credit_amount.required' => 'Credit amount is required.',
    ];

    public function mount(FinancialYear $financialYear)
    {
        $this->financialYear = $financialYear;
        $this->loadBalances();
    }

    public function loadBalances()
    {
        $query = ChartOfAccount::where('organization_id', auth()->user()->current_organization_id)
            ->with(['openingBalances' => function ($query) {
                $query->where('financial_year_id', $this->financialYear->id);
            }]);

        if ($this->account_type_filter) {
            $query->where('type', $this->account_type_filter);
        }

        $accounts = $query->orderBy('code')->get();

        $this->balances = $accounts->map(function ($account) {
            $openingBalance = $account->openingBalances->first();

            return [
                'chart_of_account_id' => $account->id,
                'account_code' => $account->code,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'debit_amount' => $openingBalance ? $openingBalance->debit_amount : 0,
                'credit_amount' => $openingBalance ? $openingBalance->credit_amount : 0,
                'description' => $openingBalance ? $openingBalance->description : null,
            ];
        })->toArray();
    }

    public function updatedAccountTypeFilter()
    {
        $this->loadBalances();
    }

    public function save()
    {
        $this->validate();

        $balancesData = [];
        foreach ($this->balances as $balance) {
            if ($balance['debit_amount'] > 0 || $balance['credit_amount'] > 0) {
                $balancesData[] = [
                    'chart_of_account_id' => $balance['chart_of_account_id'],
                    'debit_amount' => $balance['debit_amount'],
                    'credit_amount' => $balance['credit_amount'],
                    'description' => $balance['description'],
                ];
            }
        }

        $financialYearService = app(FinancialYearService::class);
        $financialYearService->setOpeningBalances($this->financialYear, $balancesData);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Opening balances saved successfully.',
        ]);

        return redirect()->route('accounting.financial-years.index');
    }

    public function render()
    {
        return view('livewire.accounting.financial-years.opening-balance-form', [
            'accountTypes' => [
                '' => 'All Accounts',
                'asset' => 'Assets',
                'liability' => 'Liabilities',
                'equity' => 'Equity',
                'revenue' => 'Revenue',
                'expense' => 'Expenses',
            ],
        ]);
    }
}
