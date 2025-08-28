<?php
// database/seeders/CsvChartOfAccountsSeeder.php

namespace Database\Seeders;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CsvChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/seedData/charts_of_accounts.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle); // Skip header row

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            [$code, $name, $type, $description] = $row;

            ChartOfAccount::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'description' => $description
                ]
            );

            $count++;
        }

        fclose($handle);
        $this->command->info("Seeded {$count} chart of accounts from CSV");
    }
}
