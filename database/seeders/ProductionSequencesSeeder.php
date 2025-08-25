<?php
// database/seeders/ProductionSequencesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionSequencesSeeder extends Seeder
{
    public function run(): void
    {
        $sequences = [
            [
                'name' => 'journal_entry_ref',
                'prefix' => 'JE-',
                'pad_length' => 6,
                'last_value' => 1000, // Start from 1000 for production
                'increment_by' => 1,
            ],
            [
                'name' => 'invoice_number',
                'prefix' => 'INV-',
                'pad_length' => 6,
                'last_value' => 500,
                'increment_by' => 1,
            ],
            [
                'name' => 'purchase_order',
                'prefix' => 'PO-',
                'pad_length' => 6,
                'last_value' => 200,
                'increment_by' => 1,
            ],
        ];

        foreach ($sequences as $sequence) {
            DB::table('sequences')->updateOrInsert(
                ['name' => $sequence['name']],
                $sequence
            );
        }
    }
}
