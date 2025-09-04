<?php

namespace App\Livewire\Accounting;

use Livewire\Component;

class JournalEntries extends Component
{
    public $entries = [];
    public $date, $description;
    public $transactions = [
        ['account' => '', 'debit' => null, 'credit' => null],
    ];

    public function mount()
    {
        // Placeholder data. In a real app, this would be fetched from the database.
        $this->entries = [
            ['id' => 1, 'date' => '2025-01-15', 'description' => 'Sale of goods', 'is_posted' => true],
            ['id' => 2, 'date' => '2025-01-16', 'description' => 'Purchase of office supplies', 'is_posted' => true],
            ['id' => 3, 'date' => '2025-01-17', 'description' => 'Rent payment for the month', 'is_posted' => false],
        ];
    }

    public function addTransaction()
    {
        $this->transactions[] = ['account' => '', 'debit' => null, 'credit' => null];
    }

    public function removeTransaction($index)
    {
        unset($this->transactions[$index]);
        $this->transactions = array_values($this->transactions); // Re-index the array
    }

    public function createEntry()
    {
        // In a real app, you would validate and save this to the database.
        // You would use your Accounting Service to ensure debits == credits.

        // Placeholder logic to add a new entry to the list.
        $newEntry = [
            'id' => count($this->entries) + 1,
            'date' => $this->date,
            'description' => $this->description,
            'is_posted' => false,
        ];
        $this->entries[] = $newEntry;

        // Reset form fields.
        $this->reset(['date', 'description', 'transactions']);
        $this->transactions = [['account' => '', 'debit' => null, 'credit' => null]];
    }

    public function render()
    {
        return view('livewire.accounting.journal-entries');
    }
}
