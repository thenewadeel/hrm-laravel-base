<?php
// database/seeders/DimensionsSeeder.php

namespace Database\Seeders;

use App\Models\Dimension;
use Illuminate\Database\Seeder;

class DimensionsSeeder extends Seeder
{
    public function run(): void
    {
        $dimensions = [
            // Departments
            ['name' => 'Production Department', 'code' => 'DEPT-PROD', 'type' => 'department', 'description' => 'Pharma manufacturing'],
            ['name' => 'Quality Control', 'code' => 'DEPT-QC', 'type' => 'department', 'description' => 'Quality assurance team'],
            ['name' => 'Research & Development', 'code' => 'DEPT-RND', 'type' => 'department', 'description' => 'Product research'],
            ['name' => 'Sales & Marketing', 'code' => 'DEPT-SALES', 'type' => 'department', 'description' => 'Sales team'],
            ['name' => 'Administration', 'code' => 'DEPT-ADMIN', 'type' => 'department', 'description' => 'Administrative staff'],

            // Projects
            ['name' => 'COVID Vaccine Project', 'code' => 'PROJ-COVax', 'type' => 'project', 'description' => 'Vaccine development'],
            ['name' => 'Diabetes Medication', 'code' => 'PROJ-DIAB', 'type' => 'project', 'description' => 'Diabetes treatment R&D'],
            ['name' => 'Factory Expansion', 'code' => 'PROJ-EXP', 'type' => 'project', 'description' => 'Manufacturing capacity increase'],

            // Locations/Branches
            ['name' => 'Dhaka Main Factory', 'code' => 'LOC-DHA', 'type' => 'branch', 'description' => 'Primary manufacturing facility'],
            ['name' => 'Chittagong Branch', 'code' => 'LOC-CHT', 'type' => 'branch', 'description' => 'Secondary operation center'],
        ];

        foreach ($dimensions as $dimension) {
            Dimension::firstOrCreate(
                ['code' => $dimension['code']],
                $dimension
            );
        }

        $this->command->info('Organizational Dimensions seeded successfully!');
    }
}
