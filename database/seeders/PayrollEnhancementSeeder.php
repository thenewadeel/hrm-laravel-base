<?php

namespace Database\Seeders;

use App\Models\AllowanceType;
use App\Models\DeductionType;
use App\Models\TaxBracket;
use Illuminate\Database\Seeder;

class PayrollEnhancementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizationId = 1; // Assuming first organization

        // Create Allowance Types
        $allowanceTypes = [
            [
                'name' => 'Housing Allowance',
                'code' => 'HA',
                'description' => 'Monthly housing allowance for employees',
                'calculation_type' => 'fixed_amount',
                'default_value' => 500.00,
                'is_taxable' => true,
                'is_recurring' => true,
                'account_code' => '5002',
            ],
            [
                'name' => 'Transport Allowance',
                'code' => 'TA',
                'description' => 'Monthly transport allowance',
                'calculation_type' => 'fixed_amount',
                'default_value' => 200.00,
                'is_taxable' => true,
                'is_recurring' => true,
                'account_code' => '5003',
            ],
            [
                'name' => 'Medical Allowance',
                'code' => 'MA',
                'description' => 'Monthly medical allowance',
                'calculation_type' => 'fixed_amount',
                'default_value' => 150.00,
                'is_taxable' => false,
                'is_recurring' => true,
                'account_code' => '5004',
            ],
            [
                'name' => 'Meal Allowance',
                'code' => 'MEA',
                'description' => 'Daily meal allowance',
                'calculation_type' => 'fixed_amount',
                'default_value' => 100.00,
                'is_taxable' => true,
                'is_recurring' => true,
                'account_code' => '5005',
            ],
            [
                'name' => 'Performance Bonus',
                'code' => 'PB',
                'description' => 'Quarterly performance bonus',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 10.00,
                'is_taxable' => true,
                'is_recurring' => false,
                'account_code' => '5006',
            ],
        ];

        foreach ($allowanceTypes as $type) {
            AllowanceType::create(array_merge($type, ['organization_id' => $organizationId]));
        }

        // Create Deduction Types
        $deductionTypes = [
            [
                'name' => 'Income Tax',
                'code' => 'TAX',
                'description' => 'Monthly income tax deduction',
                'calculation_type' => 'percentage_of_gross',
                'default_value' => 15.00,
                'is_tax_exempt' => true,
                'is_recurring' => true,
                'account_code' => '2002',
            ],
            [
                'name' => 'Social Security',
                'code' => 'SS',
                'description' => 'Social security contribution',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 5.00,
                'is_tax_exempt' => true,
                'is_recurring' => true,
                'account_code' => '2003',
            ],
            [
                'name' => 'Health Insurance',
                'code' => 'HI',
                'description' => 'Health insurance premium',
                'calculation_type' => 'fixed_amount',
                'default_value' => 100.00,
                'is_tax_exempt' => true,
                'is_recurring' => true,
                'account_code' => '2004',
            ],
            [
                'name' => 'Pension Fund',
                'code' => 'PF',
                'description' => 'Pension fund contribution',
                'calculation_type' => 'percentage_of_basic',
                'default_value' => 7.00,
                'is_tax_exempt' => true,
                'is_recurring' => true,
                'account_code' => '2005',
            ],
            [
                'name' => 'Union Dues',
                'code' => 'UD',
                'description' => 'Monthly union dues',
                'calculation_type' => 'fixed_amount',
                'default_value' => 25.00,
                'is_tax_exempt' => false,
                'is_recurring' => true,
                'account_code' => '2006',
            ],
        ];

        foreach ($deductionTypes as $type) {
            DeductionType::create(array_merge($type, ['organization_id' => $organizationId]));
        }

        // Create Tax Brackets
        $taxBrackets = [
            [
                'name' => 'Tax Bracket 1',
                'min_income' => 0,
                'max_income' => 2000,
                'rate' => 10,
                'base_tax' => 0,
                'exemption_amount' => 500,
                'effective_date' => now()->startOfYear(),
            ],
            [
                'name' => 'Tax Bracket 2',
                'min_income' => 2000.01,
                'max_income' => 5000,
                'rate' => 15,
                'base_tax' => 150,
                'exemption_amount' => 500,
                'effective_date' => now()->startOfYear(),
            ],
            [
                'name' => 'Tax Bracket 3',
                'min_income' => 5000.01,
                'max_income' => 10000,
                'rate' => 20,
                'base_tax' => 600,
                'exemption_amount' => 500,
                'effective_date' => now()->startOfYear(),
            ],
            [
                'name' => 'Tax Bracket 4',
                'min_income' => 10000.01,
                'max_income' => null,
                'rate' => 25,
                'base_tax' => 1600,
                'exemption_amount' => 500,
                'effective_date' => now()->startOfYear(),
            ],
        ];

        foreach ($taxBrackets as $bracket) {
            TaxBracket::create(array_merge($bracket, ['organization_id' => $organizationId]));
        }

        $this->command->info('Payroll enhancement data seeded successfully!');
    }
}
