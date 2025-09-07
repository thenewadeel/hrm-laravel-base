<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\Transaction;
use App\Exceptions\Accounting\UnbalancedTransactionException;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class JournalEntries extends Component
{
    public $entry_date;
    public $description;
    public $transactions = [['account_id' => null, 'debit' => null, 'credit' => null], ['account_id' => null, 'debit' => null, 'credit' => null]];
    public $entries = [];
    public $accounts = [];

    protected $rules = [
        'entry_date' => 'required|date',
        'description' => 'required|string|max:255',
        'transactions.*.account_id' => 'required|exists:chart_of_accounts,id',
        'transactions.*.debit' => 'nullable|numeric|min:0',
        'transactions.*.credit' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'transactions.*.debit.required_without' => 'Either debit or credit must be entered.',
        'transactions.*.credit.required_without' => 'Either debit or credit must be entered.',
    ];

    public function mount()
    {
        $this->entry_date = now()->format('Y-m-d');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.accounting.journal-entries');
    }

    public function loadData()
    {
        $this->entries = JournalEntry::with('ledgerEntries')->latest()->get();
        $this->accounts = ChartOfAccount::orderBy('name')->get();
    }

    public function addTransaction()
    {
        $this->transactions[] = ['account_id' => null, 'debit' => null, 'credit' => null];
    }

    public function removeTransaction($index)
    {
        unset($this->transactions[$index]);
        $this->transactions = array_values($this->transactions);
    }

    public function createEntry()
    {
        // Custom validation to ensure debits and credits are balanced.
        $this->withValidator(function (Validator $validator) {
            $validator->after(function ($validator) {
                $totalDebit = collect($this->transactions)->sum('debit');
                $totalCredit = collect($this->transactions)->sum('credit');

                if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
                    $validator->errors()->add('transactions', 'Total debits must equal total credits.');
                }
            });
        })->validate();

        try {
            DB::transaction(function () {
                // Create the new Journal Entry record.
                $journalEntry = JournalEntry::createWithTransaction([
                    'entry_date' => $this->entry_date,
                    'description' => $this->description,
                ]);

                // Prepare the ledger entries for posting.
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

                        if ($amount > 0) {
                            return [
                                'account' => ChartOfAccount::find($transaction['account_id']),
                                'type' => $type,
                                'amount' => $amount,
                            ];
                        }
                        return null;
                    })
                    ->filter()
                    ->toArray();

                // Post the entries to the ledger.
                $journalEntry->post($ledgerEntries);
            });

            // Reload the entries from the database to update the view.
            $this->loadData();
            // Reset the form fields for a new entry.
            $this->reset(['entry_date', 'description', 'transactions']);
            $this->transactions = [['account_id' => null, 'debit' => null, 'credit' => null], ['account_id' => null, 'debit' => null, 'credit' => null]];
            // Flash a success message.
            session()->flash('message', 'Journal Entry created and posted successfully.');
        } catch (UnbalancedTransactionException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'An error occurred while creating the journal entry.');
        }
    }
}
