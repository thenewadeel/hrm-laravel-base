<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Support\Facades\DB;

class JSDebugTest extends JavaScriptDuskTestCase
{
    /**
     * Test database connection and migrations.
     */
    public function test_database_connection(): void
    {
        // Test if database file exists
        $this->assertTrue(file_exists(storage_path('testing_dusk_javascript.sqlite')), 'Database file should exist');
        
        // Test if we can query the migrations table
        $migrations = DB::table('migrations')->count();
        $this->assertGreaterThan(0, $migrations, 'Migrations should have been run');
    }

    /**
     * Test basic page load.
     */
    public function test_page_load(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->pause(2000)
                ->assertTitle('HRM-Base');
        });
    }
}