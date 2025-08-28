<?php
// database/seeders/CsvUsersSeeder.php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class CsvUsersSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/seedData/users.csv');

        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            [$name, $email, $password, $orgName, $unitName, $position, $rolesJson, $permissionsJson, $status] = $row;

            $organization = Organization::where('name', $orgName)->first();

            if (!$organization) {
                $this->command->warn("Organization {$orgName} not found for user {$email}");
                continue;
            }

            $unit = $unitName ?
                OrganizationUnit::where('name', $unitName)
                ->where('organization_id', $organization->id)
                ->first() : null;

            $roles = $rolesJson ? json_decode($rolesJson, true) : null;
            $permissions = $permissionsJson ? json_decode($permissionsJson, true) : null;

            // First create or update the user
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    // 'status' => $status,
                    'email_verified_at' => now()
                ]
            );

            // Then attach to organization with pivot data
            $user->organizations()->syncWithoutDetaching([
                $organization->id => [
                    'organization_unit_id' => $unit ? $unit->id : null,
                    'position' => $position,
                    'roles' => json_encode($roles),
                    'permissions' => json_encode($permissions)
                ]
            ]);

            $count++;
        }

        fclose($handle);
        $this->command->info("Seeded {$count} users from CSV");
    }
}
