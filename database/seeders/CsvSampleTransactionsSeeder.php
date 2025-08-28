<?php
// database/seeders/CsvSampleTransactionsSeeder.php

namespace Database\Seeders;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Dimension;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CsvSampleTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/seedData/sample_transactions.csv');

        if (!File::exists($csvFile)) {
            $this->command->warn("Sample transactions CSV not found, skipping...");
            return;
        }

        // Get or create a system user for these transactions
        $user = User::firstOrCreate(
            ['email' => 'system@pharma.com'],
            ['name' => 'System Account', 'password' => bcrypt('system')]
        );

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $journalEntries = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            [$date, $description, $accountCode, $type, $amount, $dimensionCode] = $row;

            // Group transactions by date and description for journal entries
            $key = $date . '-' . md5($description);

            if (!isset($journalEntries[$key])) {
                $journalEntries[$key] = JournalEntry::create([
                    'reference_number' => 'JE-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT),
                    'entry_date' => $date,
                    'description' => $description,
                    'status' => 'posted',
                    'created_by' => $user->id,
                    'posted_at' => $date,
                ]);
            }

            $account = ChartOfAccount::where('code', $accountCode)->first();
            $dimension = $dimensionCode ? Dimension::where('code', $dimensionCode)->first() : null;

            if ($account) {
                // Store the newly created LedgerEntry instance
                $createdLedgerEntry = LedgerEntry::create([
                    'entry_date' => $date,
                    'chart_of_account_id' => $account->id,
                    'type' => $type,
                    'amount' => $amount,
                    'description' => $description,
                    'transactionable_type' => JournalEntry::class,
                    'transactionable_id' => $journalEntries[$key]->id,
                ]);

                if ($dimension) {
                    // Attach dimension to the correct, just-created ledger entry
                    $createdLedgerEntry->dimensions()->attach($dimension->id);
                }

                $count++;
            }
        }

        fclose($handle);
        $this->command->info("Seeded {$count} sample transactions from CSV");
    }
}
