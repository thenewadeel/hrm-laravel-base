<?php

namespace Database\Seeders;

use App\Models\Accounting\TaxJurisdiction;
use App\Models\Accounting\TaxRate;
use App\Models\Organization;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organization = Organization::first();
        if (! $organization) {
            $this->command->error('No organization found. Please run organization seeder first.');

            return;
        }

        // Create tax jurisdictions
        $jurisdictions = [
            [
                'name' => 'Federal Tax Authority',
                'code' => 'FED',
                'type' => 'country',
                'tax_id_number' => 'FED-123456789',
                'filing_requirements' => [
                    'frequency' => 'quarterly',
                    'due_days' => 30,
                ],
            ],
            [
                'name' => 'State Revenue Department',
                'code' => 'STATE',
                'type' => 'state',
                'tax_id_number' => 'STATE-987654321',
                'filing_requirements' => [
                    'frequency' => 'monthly',
                    'due_days' => 20,
                ],
            ],
            [
                'name' => 'Local Municipal Tax',
                'code' => 'LOCAL',
                'type' => 'municipality',
                'filing_requirements' => [
                    'frequency' => 'quarterly',
                    'due_days' => 45,
                ],
            ],
        ];

        foreach ($jurisdictions as $jurisdictionData) {
            TaxJurisdiction::create(array_merge($jurisdictionData, [
                'organization_id' => $organization->id,
                'is_active' => true,
            ]));
        }

        // Create tax rates
        $federalJurisdiction = TaxJurisdiction::where('code', 'FED')->first();
        $stateJurisdiction = TaxJurisdiction::where('code', 'STATE')->first();
        $localJurisdiction = TaxJurisdiction::where('code', 'LOCAL')->first();

        $taxRates = [
            [
                'name' => 'Federal Sales Tax',
                'code' => 'FST',
                'type' => 'sales',
                'rate' => 5.0000,
                'tax_jurisdiction_id' => $federalJurisdiction->id,
                'effective_date' => now()->startOfYear(),
                'description' => 'Federal sales tax on all taxable sales',
            ],
            [
                'name' => 'State Sales Tax',
                'code' => 'SST',
                'type' => 'sales',
                'rate' => 4.5000,
                'tax_jurisdiction_id' => $stateJurisdiction->id,
                'effective_date' => now()->startOfYear(),
                'description' => 'State sales tax on retail sales',
            ],
            [
                'name' => 'Local Municipal Tax',
                'code' => 'LMT',
                'type' => 'sales',
                'rate' => 2.0000,
                'tax_jurisdiction_id' => $localJurisdiction->id,
                'effective_date' => now()->startOfYear(),
                'description' => 'Local municipal sales tax',
            ],
            [
                'name' => 'Federal Withholding Tax',
                'code' => 'FWT',
                'type' => 'withholding',
                'rate' => 10.0000,
                'tax_jurisdiction_id' => $federalJurisdiction->id,
                'effective_date' => now()->startOfYear(),
                'description' => 'Federal tax withholding on payments',
            ],
            [
                'name' => 'VAT',
                'code' => 'VAT',
                'type' => 'vat',
                'rate' => 15.0000,
                'tax_jurisdiction_id' => $federalJurisdiction->id,
                'effective_date' => now()->startOfYear(),
                'description' => 'Value Added Tax on goods and services',
            ],
            [
                'name' => 'Service Tax',
                'code' => 'SVT',
                'type' => 'service',
                'rate' => 8.0000,
                'tax_jurisdiction_id' => $stateJurisdiction->id,
                'effective_date' => now()->startOfYear(),
                'description' => 'Tax on professional services',
            ],
        ];

        foreach ($taxRates as $taxRateData) {
            TaxRate::create(array_merge($taxRateData, [
                'organization_id' => $organization->id,
                'is_active' => true,
                'is_compound' => false,
            ]));
        }

        if ($this->command) {
            $this->command->info('Tax jurisdictions and rates seeded successfully.');
        }
    }
}
