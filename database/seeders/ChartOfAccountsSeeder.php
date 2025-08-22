<?php
// database/seeders/ChartOfAccountsSeeder.php

namespace Database\Seeders;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Assets
            ['code' => '1000', 'name' => 'Current Assets', 'type' => 'asset', 'description' => 'Short-term assets'],
            ['code' => '1010', 'name' => 'Cash on Hand', 'type' => 'asset', 'description' => 'Physical cash available'],
            ['code' => '1020', 'name' => 'Bank Accounts', 'type' => 'asset', 'description' => 'Company bank accounts'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset', 'description' => 'Money owed by customers'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset', 'description' => 'Pharma product inventory'],
            ['code' => '1300', 'name' => 'Prepaid Expenses', 'type' => 'asset', 'description' => 'Prepaid costs'],

            // Liabilities
            ['code' => '2000', 'name' => 'Current Liabilities', 'type' => 'liability', 'description' => 'Short-term debts'],
            ['code' => '2010', 'name' => 'Accounts Payable', 'type' => 'liability', 'description' => 'Money owed to suppliers'],
            ['code' => '2020', 'name' => 'Short-term Loans', 'type' => 'liability', 'description' => 'Short-term borrowings'],
            ['code' => '2100', 'name' => 'Accrued Expenses', 'type' => 'liability', 'description' => 'Accrued costs'],

            // Equity
            ['code' => '3000', 'name' => 'Equity', 'type' => 'equity', 'description' => 'Owner\'s equity'],
            ['code' => '3010', 'name' => 'Common Stock', 'type' => 'equity', 'description' => 'Issued capital'],
            ['code' => '3020', 'name' => 'Retained Earnings', 'type' => 'equity', 'description' => 'Accumulated profits'],

            // Revenue
            ['code' => '4000', 'name' => 'Operating Revenue', 'type' => 'revenue', 'description' => 'Primary business revenue'],
            ['code' => '4010', 'name' => 'Pharma Product Sales', 'type' => 'revenue', 'description' => 'Medicine sales'],
            ['code' => '4020', 'name' => 'Consultation Fees', 'type' => 'revenue', 'description' => 'Medical consulting'],
            ['code' => '4100', 'name' => 'Other Revenue', 'type' => 'revenue', 'description' => 'Miscellaneous income'],

            // Expenses
            ['code' => '5000', 'name' => 'Operating Expenses', 'type' => 'expense', 'description' => 'Business operation costs'],
            ['code' => '5010', 'name' => 'Cost of Goods Sold', 'type' => 'expense', 'description' => 'Direct production costs'],
            ['code' => '5020', 'name' => 'Salaries & Wages', 'type' => 'expense', 'description' => 'Employee compensation'],
            ['code' => '5030', 'name' => 'Raw Materials', 'type' => 'expense', 'description' => 'Pharma ingredients'],
            ['code' => '5040', 'name' => 'Manufacturing Costs', 'type' => 'expense', 'description' => 'Production expenses'],
            ['code' => '5050', 'name' => 'Research & Development', 'type' => 'expense', 'description' => 'R&D expenses'],
            ['code' => '5060', 'name' => 'Marketing & Advertising', 'type' => 'expense', 'description' => 'Promotion costs'],
            ['code' => '5070', 'name' => 'Utilities', 'type' => 'expense', 'description' => 'Electricity, water, etc.'],
            ['code' => '5080', 'name' => 'Rent', 'type' => 'expense', 'description' => 'Facility rental'],
            ['code' => '5090', 'name' => 'Depreciation', 'type' => 'expense', 'description' => 'Asset depreciation'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::firstOrCreate(
                ['code' => $account['code']],
                $account
            );
        }

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
