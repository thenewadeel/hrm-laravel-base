<?php

namespace App\Livewire\Accounting\BankStatements;

use App\Models\Accounting\BankAccount;
use App\Services\BankReconciliationService;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class Import extends Component
{
    use WithFileUploads;

    #[Validate('required|exists:bank_accounts,id')]
    public $bankAccountId;

    #[Validate('required|file|mimes:csv,txt,xlsx,xls|max:10240')]
    public $statementFile;

    #[Validate('required|string|max:255')]
    public $statementNumber;

    #[Validate('required|date')]
    public $statementDate;

    #[Validate('required|date')]
    public $periodStartDate;

    #[Validate('required|date')]
    public $periodEndDate;

    #[Validate('required|numeric|min:0')]
    public $openingBalance;

    #[Validate('required|numeric|min:0')]
    public $closingBalance;

    #[Validate('nullable|string|max:1000')]
    public $notes;

    public $importPreview = [];

    public $showPreview = false;

    public $isImporting = false;

    protected $reconciliationService;

    public function boot(BankReconciliationService $reconciliationService)
    {
        $this->reconciliationService = $reconciliationService;
    }

    public function render()
    {
        return view('livewire.accounting.bank-statements.import', [
            'bankAccounts' => BankAccount::active()->get(),
        ]);
    }

    public function updatedStatementFile()
    {
        $this->validateOnly('statementFile');
        $this->processFile();
    }

    public function processFile()
    {
        if (! $this->statementFile) {
            return;
        }

        $this->isImporting = true;

        try {
            $filePath = $this->statementFile->getRealPath();
            $extension = $this->statementFile->getClientOriginalExtension();

            $transactions = [];

            if ($extension === 'csv') {
                $transactions = $this->processCsvFile($filePath);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $transactions = $this->processExcelFile($filePath);
            }

            $this->importPreview = array_slice($transactions, 0, 10);
            $this->showPreview = true;

            $this->dispatch('file-processed', [
                'total_transactions' => count($transactions),
                'preview' => $this->importPreview,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Error processing file: '.$e->getMessage(),
            ]);
        } finally {
            $this->isImporting = false;
        }
    }

    public function processCsvFile($filePath): array
    {
        $transactions = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \Exception('Cannot open file');
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            throw new \Exception('Cannot read file header');
        }

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= 4) {
                $transactions[] = [
                    'transaction_date' => $this->parseDate($row[0]),
                    'description' => $row[1],
                    'transaction_type' => $this->determineTransactionType($row[2]),
                    'amount' => abs(floatval($row[2])),
                    'transaction_number' => $row[3] ?? null,
                    'reference_number' => $row[4] ?? null,
                    'balance_after' => isset($row[5]) ? floatval($row[5]) : null,
                ];
            }
        }

        fclose($handle);

        return $transactions;
    }

    public function processExcelFile($filePath): array
    {
        $transactions = [];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $header = array_shift($rows);

            foreach ($rows as $row) {
                if (count($row) >= 4 && ! empty($row[0])) {
                    $transactions[] = [
                        'transaction_date' => $this->parseDate($row[0]),
                        'description' => $row[1],
                        'transaction_type' => $this->determineTransactionType($row[2]),
                        'amount' => abs(floatval($row[2])),
                        'transaction_number' => $row[3] ?? null,
                        'reference_number' => $row[4] ?? null,
                        'balance_after' => isset($row[5]) ? floatval($row[5]) : null,
                    ];
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Error processing Excel file: '.$e->getMessage());
        }

        return $transactions;
    }

    public function parseDate($date): string
    {
        if (is_numeric($date)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    public function determineTransactionType($amount): string
    {
        return floatval($amount) < 0 ? 'debit' : 'credit';
    }

    public function importStatement()
    {
        $this->validate();

        if (! $this->statementFile) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Please select a statement file.',
            ]);

            return;
        }

        try {
            $filePath = $this->statementFile->getRealPath();
            $extension = $this->statementFile->getClientOriginalExtension();

            $transactions = [];

            if ($extension === 'csv') {
                $transactions = $this->processCsvFile($filePath);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                $transactions = $this->processExcelFile($filePath);
            }

            $statementData = [
                'statement_number' => $this->statementNumber,
                'statement_date' => $this->statementDate,
                'period_start_date' => $this->periodStartDate,
                'period_end_date' => $this->periodEndDate,
                'opening_balance' => $this->openingBalance,
                'closing_balance' => $this->closingBalance,
                'transactions' => $transactions,
                'notes' => $this->notes,
                'file_path' => $this->statementFile->store('bank-statements'),
            ];

            $bankAccount = BankAccount::find($this->bankAccountId);
            $statement = $this->reconciliationService->importBankStatement($bankAccount, $statementData);

            $this->dispatch('show-message', [
                'type' => 'success',
                'message' => "Bank statement imported successfully with {$statement->transaction_count} transactions.",
            ]);

            $this->resetForm();

        } catch (\Exception $e) {
            $this->dispatch('show-message', [
                'type' => 'error',
                'message' => 'Error importing statement: '.$e->getMessage(),
            ]);
        }
    }

    public function resetForm()
    {
        $this->reset([
            'bankAccountId', 'statementFile', 'statementNumber', 'statementDate',
            'periodStartDate', 'periodEndDate', 'openingBalance', 'closingBalance',
            'notes', 'importPreview', 'showPreview',
        ]);
    }

    public function cancel()
    {
        return $this->redirect(route('accounting.bank-statements.index'), navigate: true);
    }
}
