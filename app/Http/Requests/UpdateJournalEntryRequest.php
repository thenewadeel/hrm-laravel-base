<?php
// app/Http/Requests/UpdateJournalEntryRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Keep simple for testing
    }

    public function rules(): array
    {
        // Only allow updating description for posted entries
        $journalEntry = $this->route('journal_entry');

        if ($journalEntry && $journalEntry->status === 'posted') {
            return [
                'description' => ['sometimes', 'string', 'max:500'],
            ];
        }

        // For draft entries, allow full editing
        return [
            'entry_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'description' => ['sometimes', 'string', 'max:500'],
            'entries' => ['sometimes', 'array', 'min:2'],
            'entries.*.account_id' => [
                'required_with:entries',
                'exists:chart_of_accounts,id'
            ],
            'entries.*.type' => ['required_with:entries', Rule::in(['debit', 'credit'])],
            'entries.*.amount' => ['required_with:entries', 'numeric', 'min:0.01', 'max:9999999.99'],
            'entries.*.description' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.*' => ['exists:dimensions,id'],
        ];
    }

    public function withValidator($validator)
    {
        // Same validation logic as StoreJournalEntryRequest
        $validator->after(function ($validator) {
            if ($this->has('entries')) {
                $entries = $this->input('entries', []);

                $totalDebits = 0;
                $totalCredits = 0;

                foreach ($entries as $index => $entry) {
                    if ($entry['type'] === 'debit') {
                        $totalDebits += (float) $entry['amount'];
                    } else {
                        $totalCredits += (float) $entry['amount'];
                    }
                }

                if (abs($totalDebits - $totalCredits) > 0.001) {
                    $validator->errors()->add(
                        'entries',
                        "Debits must equal credits. Difference: " . abs($totalDebits - $totalCredits)
                    );
                }
            }
        });
    }
}
