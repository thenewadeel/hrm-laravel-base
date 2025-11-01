<?php
// app/Http/Requests/StoreJournalEntryRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Keep simple for testing, add auth later
    }

    public function rules(): array
    {
        return [
            'organization_id' => ['required', 'exists:organizations,id'],
            'entry_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:500'],
            'entries' => ['required', 'array', 'min:2'], // At least 2 entries (debit + credit)
            'entries.*.account_id' => [
                'required',
                'exists:chart_of_accounts,id',
                // Add custom validation to check account type vs entry type
            ],
            'entries.*.type' => ['required', Rule::in(['debit', 'credit'])],
            'entries.*.amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'entries.*.description' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'array'],
            'dimensions.*' => ['exists:dimensions,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'entry_date.required' => 'The entry date is required.',
            'entry_date.before_or_equal' => 'The entry date cannot be in the future.',
            'description.required' => 'A description is required for the journal entry.',
            'entries.required' => 'At least two ledger entries are required.',
            'entries.min' => 'A journal entry must have at least one debit and one credit.',
            'entries.*.account_id.required' => 'Each entry must have an account.',
            'entries.*.account_id.exists' => 'The selected account is invalid.',
            'entries.*.type.required' => 'Each entry must specify debit or credit.',
            'entries.*.type.in' => 'Entry type must be either debit or credit.',
            'entries.*.amount.required' => 'Each entry must have an amount.',
            'entries.*.amount.numeric' => 'Amount must be a valid number.',
            'entries.*.amount.min' => 'Amount must be at least 0.01.',
            'entries.*.amount.max' => 'Amount cannot exceed 9,999,999.99.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation: Check that debits equal credits
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
                    "Debits ({$totalDebits}) must equal credits ({$totalCredits}). Difference: " . abs($totalDebits - $totalCredits)
                );
            }

            // Custom validation: Check account types are valid for debit/credit
            foreach ($entries as $index => $entry) {
                if (!isset($entry['account_id'])) {
                    continue;
                }

                $account = \App\Models\Accounting\ChartOfAccount::find($entry['account_id']);
                if (!$account) {
                    continue;
                }

                $validDebitAccounts = ['asset', 'expense'];
                $validCreditAccounts = ['liability', 'equity', 'revenue'];

                if ($entry['type'] === 'debit' && !in_array($account->type, $validDebitAccounts)) {
                    $validator->errors()->add(
                        "entries.{$index}.type",
                        "Cannot debit a {$account->type} account. Only asset and expense accounts can be debited."
                    );
                }

                if ($entry['type'] === 'credit' && !in_array($account->type, $validCreditAccounts)) {
                    $validator->errors()->add(
                        "entries.{$index}.type",
                        "Cannot credit a {$account->type} account. Only liability, equity, and revenue accounts can be credited."
                    );
                }
            }
        });
    }
}
