<?php
// database/seeders/CsvOrganizationsSeeder.php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CsvOrganizationsSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/seedData/organizations.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            [$name, $description] = $row;

            Organization::firstOrCreate(
                ['name' => $name],
                [
                    'description' => $description,
                    'is_active' => true
                ]
            );

            $count++;
        }

        fclose($handle);
        $this->command->info("Seeded {$count} organizations from CSV");
    }
}
