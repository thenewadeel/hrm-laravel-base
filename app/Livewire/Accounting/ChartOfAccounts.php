<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On; // Import the On attribute

class ChartOfAccounts extends Component
{
    public Collection $accounts;

    // Public properties for the create form
    public $code;
    public $name;
    public $type;
    public $description;

    // Public properties for the update form
    public $editingAccount;

    protected $rules = [
        'code' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'type' => 'required|string|in:asset,liability,equity,revenue,expense',
        'description' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadAccounts();
    }

    public function loadAccounts()
    {
        $this->accounts = ChartOfAccount::orderBy('code')->get();
    }

    public function render()
    {
        return view('livewire.accounting.chart-of-accounts');
    }

    public function create()
    {
        $this->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('chart_of_accounts')],
        ]);

        ChartOfAccount::create([
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
        ]);

        $this->loadAccounts();
        $this->reset(['code', 'name', 'type', 'description']);
        session()->flash('message', 'Chart of Account created successfully.');
    }

    #[On('edit-account')]
    public function edit(ChartOfAccount $account)
    {
        $this->editingAccount = $account;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->description = $account->description;
    }

    public function update()
    {
        $this->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('chart_of_accounts')->ignore($this->editingAccount)],
        ]);

        $this->editingAccount->update([
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
        ]);

        $this->loadAccounts();
        $this->reset(['code', 'name', 'type', 'description', 'editingAccount']);
        session()->flash('message', 'Chart of Account updated successfully.');
    }

    public function cancelEdit()
    {
        $this->reset(['code', 'name', 'type', 'description', 'editingAccount']);
    }
}
