<?php
// database/seeders/SampleTransactionsSeeder.php

namespace Database\Seeders;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\LedgerEntry;
use App\Models\Dimension;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        // Get accounts
        $cash = ChartOfAccount::where('code', '1010')->first();
        $bank = ChartOfAccount::where('code', '1020')->first();
        $ar = ChartOfAccount::where('code', '1100')->first();
        $sales = ChartOfAccount::where('code', '4010')->first();
        $cogs = ChartOfAccount::where('code', '5010')->first();
        $salaries = ChartOfAccount::where('code', '5020')->first();

        // Get dimensions
        $productionDept = Dimension::where('code', 'DEPT-PROD')->first();
        $salesDept = Dimension::where('code', 'DEPT-SALES')->first();

        // Get or create a user
        $user = User::firstOrCreate(
            ['email' => 'accountant@pharma.com'],
            ['name' => 'Company Accountant', 'password' => bcrypt('password')]
        );

        // 1. Initial capital injection
        $je1 = JournalEntry::create([
            'reference_number' => 'JE-0001',
            'entry_date' => now()->subMonths(3),
            'description' => 'Initial capital investment',
            'status' => 'posted',
            'created_by' => $user->id,
            'posted_at' => now()->subMonths(3),
        ]);

        LedgerEntry::create([
            'chart_of_account_id' => $bank->id,
            'type' => 'debit',
            'amount' => 1000000.00,
            'entry_date' => now()->subMonths(3),
            'description' => 'Initial capital deposit',
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $je1->id,
        ]);

        LedgerEntry::create([
            'chart_of_account_id' => ChartOfAccount::where('code', '3010')->first()->id,
            'type' => 'credit',
            'amount' => 1000000.00,
            'entry_date' => now()->subMonths(3),
            'description' => 'Common stock issuance',
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $je1->id,
        ]);

        // 2. Pharma product sale
        $je2 = JournalEntry::create([
            'reference_number' => 'JE-0002',
            'entry_date' => now()->subMonth(),
            'description' => 'Bulk medicine sale to hospital',
            'status' => 'posted',
            'created_by' => $user->id,
            'posted_at' => now()->subMonth(),
        ]);

        LedgerEntry::create([
            'chart_of_account_id' => $ar->id,
            'type' => 'debit',
            'amount' => 250000.00,
            'entry_date' => now()->subMonth(),
            'description' => 'Invoice #INV-001',
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $je2->id,
        ])->dimensions()->attach($salesDept->id);

        LedgerEntry::create([
            'chart_of_account_id' => $sales->id,
            'type' => 'credit',
            'amount' => 250000.00,
            'entry_date' => now()->subMonth(),
            'description' => 'Pharma product revenue',
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $je2->id,
        ])->dimensions()->attach($salesDept->id);

        // 3. Salary payment
        $je3 = JournalEntry::create([
            'reference_number' => 'JE-0003',
            'entry_date' => now()->subDays(15),
            'description' => 'Monthly payroll',
            'status' => 'posted',
            'created_by' => $user->id,
            'posted_at' => now()->subDays(15),
        ]);

        LedgerEntry::create([
            'chart_of_account_id' => $bank->id,
            'type' => 'credit',
            'amount' => 150000.00,
            'entry_date' => now()->subDays(15),
            'description' => 'Salary payments',
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $je3->id,
        ]);

        LedgerEntry::create([
            'chart_of_account_id' => $salaries->id,
            'type' => 'debit',
            'amount' => 150000.00,
            'entry_date' => now()->subDays(15),
            'description' => 'Employee salaries',
            'transactionable_type' => JournalEntry::class,
            'transactionable_id' => $je3->id,
        ])->dimensions()->attach($productionDept->id);

        $this->command->info('Sample transactions seeded successfully!');
        $this->command->info('Generated: Initial capital, Product sale, and Payroll transactions');
    }
}
