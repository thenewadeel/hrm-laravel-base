<?php
// app/Exceptions/InvalidAccountTypeException.php

namespace App\Exceptions;

use App\Models\Accounting\ChartOfAccount;
use Exception;

class InvalidAccountTypeException extends Exception
{
    protected $account;
    protected $attemptedEntryType;

    public function __construct(ChartOfAccount $account, string $attemptedEntryType, string $message = null)
    {
        $this->account = $account;
        $this->attemptedEntryType = $attemptedEntryType;

        $message = $message ?? $this->generateDefaultMessage();

        parent::__construct($message, 422); // 422 Unprocessable Entity is appropriate for invalid data
    }

    protected function generateDefaultMessage(): string
    {
        $validTypes = $this->attemptedEntryType === 'debit'
            ? ['asset', 'expense']
            : ['liability', 'equity', 'revenue'];

        return sprintf(
            'Cannot %s a %s account (ID: %s, Code: %s). Valid account types for %s entries are: %s',
            $this->attemptedEntryType,
            $this->account->type,
            $this->account->id,
            $this->account->code,
            $this->attemptedEntryType,
            implode(', ', $validTypes)
        );
    }

    public function getAccount(): ChartOfAccount
    {
        return $this->account;
    }

    public function getAttemptedEntryType(): string
    {
        return $this->attemptedEntryType;
    }

    public function context(): array
    {
        return [
            'account_id' => $this->account->id,
            'account_code' => $this->account->code,
            'account_type' => $this->account->type,
            'attempted_entry_type' => $this->attemptedEntryType,
            'valid_account_types' => $this->attemptedEntryType === 'debit'
                ? ['asset', 'expense']
                : ['liability', 'equity', 'revenue']
        ];
    }
}
