<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ]);


        $this->call([
            // Organizational structure
            CsvOrganizationsSeeder::class,
            CsvOrganizationUnitsSeeder::class,
            CsvUsersSeeder::class,

            // CSV-based seeders
            CsvChartOfAccountsSeeder::class,
            CsvDimensionsSeeder::class,
            CsvSampleTransactionsSeeder::class,

            // Factory-based seeders (for testing)
            // ChartOfAccountsSeeder::class, // Keep your existing factory seeder
            // DimensionsSeeder::class,
            // SampleTransactionsSeeder::class,
        ]);
    }
}
