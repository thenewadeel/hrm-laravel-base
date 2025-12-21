<?php

namespace Tests\Browser\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

trait HandlesDatabaseIsolation
{
    /**
     * Get a unique database file name for this test.
     */
    protected function getUniqueDatabaseFile(): string
    {
        $className = class_basename(static::class);
        $testName = $this->name(); // Dusk uses $this->name() property
        $hash = substr(md5($className . $testName), 0, 8);

        return storage_path("testing_dusk_{$className}_{$testName}_{$hash}.sqlite");
    }

    /**
     * Configure database for this specific test.
     */
    protected function configureIsolatedDatabase(): void
    {
        $dbFile = $this->getUniqueDatabaseFile();

        config(['database.default' => 'testing_sqlite']);
        config(['database.connections.sqlite.database' => $dbFile]);
        config(['database.connections.sqlite.foreign_key_constraints' => true]);
        config(['database.connections.sqlite.busy_timeout' => 5000]);
        config(['database.connections.sqlite.wal_mode' => false]); // Disable WAL to prevent locking
    }

    /**
     * Clean up database file for this test.
     */
    protected function cleanupIsolatedDatabase(): void
    {
        $dbFile = $this->getUniqueDatabaseFile();

        if (file_exists($dbFile)) {
            File::delete($dbFile);
        }

        // Also clean up WAL and SHM files if they exist
        $walFile = $dbFile . '-wal';
        $shmFile = $dbFile . '-shm';

        if (file_exists($walFile)) {
            File::delete($walFile);
        }

        if (file_exists($shmFile)) {
            File::delete($shmFile);
        }
    }

    /**
     * Wait for database to be available.
     */
    protected function waitForDatabase(): void
    {
        $maxRetries = 30;
        $retryDelay = 200; // milliseconds

        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                // Reconnect to database to ensure we get the latest config
                \DB::purge('sqlite');
                \DB::connection('sqlite')->getPdo();

                // Test with a simple query to ensure database is writable
                \DB::connection('sqlite')->statement('SELECT 1');
                break;
            } catch (\Exception $e) {
                if ($i === $maxRetries - 1) {
                    throw new \Exception("Failed to connect to database after {$maxRetries} attempts: " . $e->getMessage());
                }
                usleep($retryDelay * 1000);
            }
        }
    }

    /**
     * Ensure database is properly closed before cleanup.
     */
    protected function closeDatabaseConnections(): void
    {
        try {
            \DB::connection()->disconnect();
        } catch (\Exception $e) {
            // Ignore disconnection errors
        }
    }
}
