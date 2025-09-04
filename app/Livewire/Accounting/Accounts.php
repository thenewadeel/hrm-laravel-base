<?php

namespace App\Livewire\Accounting;

use Livewire\Component;

class Accounts extends Component
{
    public $accounts = [];

    public $name, $type, $is_active = true;

    // A placeholder for the form state.
    public $showForm = false;

    public function mount()
    {
        // Placeholder data. In a real app, this would be fetched from the database.
        $this->accounts = [
            ['id' => 1, 'name' => 'Cash', 'type' => 'Asset', 'is_active' => true],
            ['id' => 2, 'name' => 'Accounts Receivable', 'type' => 'Asset', 'is_active' => true],
            ['id' => 3, 'name' => 'Office Supplies', 'type' => 'Asset', 'is_active' => true],
            ['id' => 4, 'name' => 'Accounts Payable', 'type' => 'Liability', 'is_active' => true],
            ['id' => 5, 'name' => 'Capital', 'type' => 'Equity', 'is_active' => true],
            ['id' => 6, 'name' => 'Sales Revenue', 'type' => 'Revenue', 'is_active' => true],
            ['id' => 7, 'name' => 'Rent Expense', 'type' => 'Expense', 'is_active' => true],
        ];
    }

    public function createAccount()
    {
        // In a real app, you would validate and save to the database.
        // e.g., $this->validate(['name' => 'required', 'type' => 'required']);
        // Then, Account::create(...);

        $newAccount = [
            'id' => count($this->accounts) + 1,
            'name' => $this->name,
            'type' => $this->type,
            'is_active' => $this->is_active,
        ];
        $this->accounts[] = $newAccount;

        // Reset form fields and hide the form.
        $this->reset(['name', 'type', 'is_active', 'showForm']);
    }

    public function render()
    {
        return view('livewire.accounting.accounts');
    }
}
