<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Exceptions\Accounting\UnbalancedTransactionException;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalEntries extends Component
{
    use WithPagination;

    public $entry_date;
    public $description;
    public $transactions = [];
    public $accounts;

    public $is_balanced = false;
    public $total_debits = 0;
    public $total_credits = 0;

    protected $rules = [
        'entry_date' => 'required|date',
        'description' => 'required|string|max:255',
        'transactions' => 'required|array|min:2',
        'transactions.*.account_id' => 'required|exists:chart_of_accounts,id',
        'transactions.*.debit' => 'nullable|numeric|min:0|required_without:transactions.*.credit',
        'transactions.*.credit' => 'nullable|numeric|min:0|required_without:transactions.*.debit',
    ];

    protected $messages = [
        'transactions.required' => 'At least two transactions are required.',
        'transactions.min' => 'At least two transactions are required.',
        'transactions.*.account_id.required' => 'Account is required.',
        'transactions.*.debit.required_without' => 'Either debit or credit must be entered.',
        'transactions.*.credit.required_without' => 'Either debit or credit must be entered.',
    ];

    public function mount()
    {
        $this->entry_date = now()->format('Y-m-d');
        $this->addTransaction();
        $this->addTransaction();
        $this->loadAccounts();
    }

    public function render()
    {
        try {
            $entries = JournalEntry::with(['ledgerEntries.account', 'createdBy'])
                ->latest()
                ->paginate(10);
        } catch (\Exception $e) {
            // Log the error and show empty results
            Log::error('Error loading journal entries: ' . $e->getMessage());
            $entries = collect([]); // Empty collection as fallback
        }

        return view('livewire.accounting.journal-entries', [
            'entries' => $entries
        ]);
    }

    public function loadAccounts()
    {
        try {
            // Cache accounts to reduce database queries
            $this->accounts = cache()->remember('chart_of_accounts', 3600, function () {
                return ChartOfAccount::orderBy('name')->get();
            });
        } catch (\Exception $e) {
            Log::error('Error loading accounts: ' . $e->getMessage());
            $this->accounts = collect([]); // Empty collection as fallback
        }
    }

    public function addTransaction()
    {
        $this->transactions[] = [
            'account_id' => null,
            'debit' => null,
            'credit' => null,
            'error' => null
        ];
    }

    public function removeTransaction($index)
    {
        if (count($this->transactions) > 2) {
            unset($this->transactions[$index]);
            $this->transactions = array_values($this->transactions);
            $this->calculateTotals();
        }
    }

    public function updatedTransactions($value, $key)
    {
        // Parse the field path (e.g., "transactions.0.debit")
        $path = explode('.', $key);

        if (count($path) === 3) {
            $index = $path[1];
            $field = $path[2];

            // Ensure only debit or credit has value, not both
            if ($field === 'debit' && !empty($value)) {
                $this->transactions[$index]['credit'] = null;
            } elseif ($field === 'credit' && !empty($value)) {
                $this->transactions[$index]['debit'] = null;
            }

            // Validate individual transaction
            $this->validateTransaction($index);
        }

        $this->calculateTotals();
    }

    protected function validateTransaction($index)
    {
        $transaction = $this->transactions[$index];
        $this->transactions[$index]['error'] = null;

        if (empty($transaction['account_id'])) {
            return;
        }

        if (empty($transaction['debit']) && empty($transaction['credit'])) {
            $this->transactions[$index]['error'] = 'Either debit or credit must be entered.';
        } elseif (!empty($transaction['debit']) && !empty($transaction['credit'])) {
            $this->transactions[$index]['error'] = 'Only debit or credit can be entered, not both.';
        }
    }

    public function calculateTotals()
    {
        $this->total_debits = collect($this->transactions)
            ->sum(function ($transaction) {
                return (float) ($transaction['debit'] ?? 0);
            });

        $this->total_credits = collect($this->transactions)
            ->sum(function ($transaction) {
                return (float) ($transaction['credit'] ?? 0);
            });

        $this->is_balanced = abs($this->total_debits - $this->total_credits) < 0.01;
    }

    public function createEntry()
    {
        // Validate basic rules first
        $this->validate();

        // Custom validation for balanced transaction
        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                if (!$this->is_balanced) {
                    $validator->errors()->add('transactions', 'Total debits must equal total credits.');
                }

                // Check for transactions with errors
                foreach ($this->transactions as $index => $transaction) {
                    if (!empty($transaction['error'])) {
                        $validator->errors()->add("transactions.$index.account_id", $transaction['error']);
                    }
                }
            });
        })->validate();

        try {
            DB::transaction(function () {
                // Prepare the ledger entries for posting
                $ledgerEntries = collect($this->transactions)
                    ->map(function ($transaction) {
                        $type = null;
                        $amount = 0;

                        if (!empty($transaction['debit']) && $transaction['debit'] > 0) {
                            $type = 'debit';
                            $amount = $transaction['debit'];
                        } elseif (!empty($transaction['credit']) && $transaction['credit'] > 0) {
                            $type = 'credit';
                            $amount = $transaction['credit'];
                        }

                        return [
                            'account' => ChartOfAccount::find($transaction['account_id']),
                            'type' => $type,
                            'amount' => $amount,
                        ];
                    })
                    ->filter(function ($entry) {
                        return $entry['amount'] > 0;
                    })
                    ->toArray();

                // Create the journal entry and post transactions
                $journalEntry = JournalEntry::createWithTransaction([
                    'entry_date' => $this->entry_date,
                    'description' => $this->description,
                    'created_by' => auth()->id(), // Make sure you're tracking who created the entry
                ]);

                $journalEntry->post($ledgerEntries);
            });

            // Reload data and reset form
            $this->resetForm();

            // Flash success message
            session()->flash('message', 'Journal Entry created and posted successfully.');
        } catch (UnbalancedTransactionException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Journal entry creation failed: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while creating the journal entry: ' . $e->getMessage());
        }
    }

    protected function resetForm()
    {
        $this->reset(['description', 'transactions']);
        $this->entry_date = now()->format('Y-m-d');
        $this->addTransaction();
        $this->addTransaction();
        $this->calculateTotals();
    }
}
