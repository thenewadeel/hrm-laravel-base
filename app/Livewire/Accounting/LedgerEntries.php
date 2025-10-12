<?php

namespace App\Livewire\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Exceptions\UnbalancedTransactionException;
use App\Exceptions\InvalidAccountTypeException;
use App\Models\Accounting\LedgerEntry;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LedgerEntries extends Component
{
    use WithPagination;
    /**
     * @var Collection|array The collection or array of ledger entry data to display.
     */
    public $entries, $title, $subTitle;

    /**
     * Define the headers for the reusable data table component.
     * The keys must match the keys in the processed data.
     */
    public $headers = [
        'entry_date' => 'Date',
        'description' => 'Description',
        // 'account_id' => 'Account ID',
        'account_name' => 'Account Name',
        'debit_amount' => 'Debit',
        'credit_amount' => 'Credit',
    ];

    /**
     * Define the column types for the reusable data table component.
     */
    public $columnTypes = [
        'entry_date' => 'date',
        'debit_amount' => 'currency',
        'credit_amount' => 'currency',
    ];

    public function mount($title = null, $subTitle = null, $entries = null)
    {
        if (isset($title) && isset($subTitle)) {
            $this->title = $title;
            $this->subTitle = $subTitle;
        }
        // Ensure that entries is a Collection for easier handling in the view/logic,
        // especially if it's passed as a JSON string or raw array.
        $this->entries = is_array($entries) ? collect($entries) : $entries;

        // Ensure the entries are not null and are iterable
        if (!$this->entries instanceof Collection) {
            // $this->entries = collect([]);
            // $this->loadDefaultAccounts();
        }
    }

    public function render()
    {


        return view('livewire.accounting.ledger-entries', [
            'entries' => $this->entries
        ]);
    }

    public function loadDefaultAccounts()
    {
        try {
            $this->entries = LedgerEntry::latest()->get();
        } catch (\Exception $e) {
            // Log the error and show empty results
            Log::error('Error loading ledger entries: ' . $e->getMessage());
            $this->entries = collect([]); // Empty collection as fallback
        }
    }




    /**
     * Computed property to process entries for the data table.
     */
    public function getProcessedEntriesProperty()
    {
        return $this->entries->map(function ($entry) {
            $amount = (float)($entry['amount'] ?? 0);
            $type = $entry['type'] ?? '';

            return [
                'id' => $entry['id'] ?? null,
                'entry_date' => $entry['entry_date'] ?? null,
                'description' => $entry['description'] ?? '—',
                'account_name' => ChartOfAccount::find($entry['chart_of_account_id'])?->name ?? $entry['chart_of_account_id'] ?? 'N/A',
                // Split the single amount field into Debit and Credit columns
                'debit_amount' => $type === 'debit' ? $amount : 0.00,
                'credit_amount' => $type === 'credit' ? $amount : 0.00,
            ];
        })->toArray();
    }
}
