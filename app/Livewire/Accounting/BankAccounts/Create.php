<?php

namespace App\Livewire\Accounting\BankAccounts;

use App\Models\Accounting\BankAccount;
use App\Models\Accounting\ChartOfAccount;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Create extends Component
{
    #[Validate('required|exists:chart_of_accounts,id')]
    public $chart_of_account_id = '';

    #[Validate('required|string|max:255')]
    public $account_number = '';

    #[Validate('required|string|max:255')]
    public $account_name = '';

    #[Validate('required|string|max:255')]
    public $bank_name = '';

    #[Validate('nullable|string|max:255')]
    public $branch_name = '';

    #[Validate('nullable|string|max:50')]
    public $routing_number = '';

    #[Validate('nullable|string|max:20')]
    public $swift_code = '';

    #[Validate('required|string|size:3')]
    public $currency = 'USD';

    #[Validate('required|numeric|min:0')]
    public $opening_balance = 0;

    #[Validate('nullable|date')]
    public $opening_balance_date;

    #[Validate('required|in:checking,savings,money_market,cd')]
    public $account_type = 'checking';

    #[Validate('required|in:active,inactive,closed')]
    public $status = 'active';

    #[Validate('nullable|string|max:1000')]
    public $notes = '';

    public function mount()
    {
        $this->opening_balance_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $bankAccounts = ChartOfAccount::query()
            ->where('organization_id', auth()->user()->current_organization_id)
            ->where('type', 'asset')
            ->orderBy('name')
            ->get();

        return view('livewire.accounting.bank-accounts.create', [
            'bankAccounts' => $bankAccounts,
            'accountTypes' => [
                'checking' => 'Checking Account',
                'savings' => 'Savings Account',
                'money_market' => 'Money Market Account',
                'cd' => 'Certificate of Deposit',
            ],
            'currencies' => [
                'USD' => 'US Dollar',
                'EUR' => 'Euro',
                'GBP' => 'British Pound',
                'JPY' => 'Japanese Yen',
            ],
        ]);
    }

    protected function rules(): array
    {
        return [
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'account_number' => 'required|string|max:255',
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'routing_number' => 'nullable|string|max:50',
            'swift_code' => 'nullable|string|max:20',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'required|numeric|min:0',
            'opening_balance_date' => 'nullable|date',
            'account_type' => 'required|in:checking,savings,money_market,cd',
            'status' => 'required|in:active,inactive,closed',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function save()
    {
        $validated = $this->validate();

        BankAccount::create([
            'organization_id' => auth()->user()->current_organization_id,
            'chart_of_account_id' => $validated['chart_of_account_id'],
            'account_number' => $validated['account_number'],
            'account_name' => $validated['account_name'],
            'bank_name' => $validated['bank_name'],
            'branch_name' => $validated['branch_name'],
            'routing_number' => $validated['routing_number'],
            'swift_code' => $validated['swift_code'],
            'currency' => $validated['currency'],
            'opening_balance' => $validated['opening_balance'],
            'current_balance' => $validated['opening_balance'],
            'opening_balance_date' => $validated['opening_balance_date'],
            'account_type' => $validated['account_type'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ]);

        $this->dispatch('show-message', [
            'type' => 'success',
            'message' => 'Bank account created successfully.',
        ]);

        return $this->redirect(route('accounting.bank-accounts.index'), navigate: true);
    }

    public function cancel()
    {
        return $this->redirect(route('accounting.bank-accounts.index'), navigate: true);
    }
}
