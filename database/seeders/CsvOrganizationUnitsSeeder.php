<?php
// database/seeders/CsvOrganizationUnitsSeeder.php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CsvOrganizationUnitsSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/seedData/organization_units.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $units = [];
        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            [$orgName, $unitName, $type, $parentUnitName, $customFieldsJson] = $row;

            $organization = Organization::where('name', $orgName)->first();

            if (!$organization) {
                $this->command->warn("Organization {$orgName} not found for unit {$unitName}");
                continue;
            }

            $parentUnit = $parentUnitName ?
                OrganizationUnit::where('name', $parentUnitName)
                ->where('organization_id', $organization->id)
                ->first() : null;

            $customFields = $customFieldsJson ? json_decode($customFieldsJson, true) : null;

            OrganizationUnit::firstOrCreate(
                [
                    'name' => $unitName,
                    'organization_id' => $organization->id
                ],
                [
                    'type' => $type,
                    'parent_id' => $parentUnit ? $parentUnit->id : null,
                    'custom_fields' => $customFields
                ]
            );

            $count++;
        }

        fclose($handle);
        $this->command->info("Seeded {$count} organization units from CSV");
    }
}
