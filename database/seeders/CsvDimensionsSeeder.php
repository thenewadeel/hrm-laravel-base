<?php
// database/seeders/CsvDimensionsSeeder.php

namespace Database\Seeders;

use App\Models\Dimension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CsvDimensionsSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/seedData/dimensions.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle); // Skip header row

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            [$name, $code, $type, $description] = $row;

            Dimension::firstOrCreate(
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
        $this->command->info("Seeded {$count} dimensions from CSV");
    }
}
