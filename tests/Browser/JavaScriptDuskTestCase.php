<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Browser\Concerns\HandlesAlpineTesting;
use Tests\Browser\Concerns\HandlesDatabaseIsolation;
use Tests\Browser\Concerns\HandlesDrawerTesting;
use Tests\Browser\Concerns\HandlesJavaScriptTesting;
use Tests\Browser\Concerns\HandlesMultiTenantTesting;
use Tests\Browser\Concerns\HandlesSearchTesting;
use Tests\Browser\Concerns\OptimizedWaits;

abstract class JavaScriptDuskTestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use HandlesAlpineTesting;
    use HandlesDatabaseIsolation;
    use HandlesDrawerTesting;
    use HandlesJavaScriptTesting;
    use HandlesMultiTenantTesting;
    use HandlesSearchTesting;
    use OptimizedWaits;

    /**
     * The organization instance for multi-tenant testing.
     */
    protected ?Organization $organization = null;

    /**
     * The user instance for authentication testing.
     */
    protected ?User $user = null;

    /**
     * Track if migrations have been run for this test run.
     */
    protected static bool $migrated = false;

    /**
     * The database file path for this test run.
     */
    protected static string $databaseFile;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver();
        }
    }

    /**
     * Clean up after all tests have run.
     */
    #[\PHPUnit\Framework\Attributes\AfterClass]
    public static function cleanup(): void
    {
        if (isset(static::$databaseFile) && file_exists(static::$databaseFile)) {
            unlink(static::$databaseFile);

            // Clean up WAL and SHM files if they exist
            $walFile = static::$databaseFile.'-wal';
            $shmFile = static::$databaseFile.'-shm';

            if (file_exists($walFile)) {
                unlink($walFile);
            }
            if (file_exists($shmFile)) {
                unlink($shmFile);
            }
        }
    }

    /**
     * Create a RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        $capabilities = DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            $options
        );

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            $capabilities
        );
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set up environment and database configuration
        $this->setUpDuskEnvironment();

        // Only run migrations once per test suite
        if (! static::$migrated) {
            $this->runMigrations();
            static::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * Set up the Dusk environment configuration.
     */
    protected function setUpDuskEnvironment(): void
    {
        // Set proper environment for Dusk tests
        app()->detectEnvironment(function () {
            return 'dusk';
        });

        // Set configuration directly
        config(['app.name' => 'HRM-Base']);
        config(['app.env' => 'dusk']);
        config(['app.debug' => true]);

        // Configure database for Dusk tests
        $this->configureDuskDatabase();
    }

    /**
     * Configure database for Dusk tests.
     */
    protected function configureDuskDatabase(): void
    {
        if (! isset(static::$databaseFile)) {
            static::$databaseFile = storage_path('testing_dusk_js.sqlite');
        }

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => static::$databaseFile]);
        config(['database.connections.sqlite.foreign_key_constraints' => true]);
        config(['database.connections.sqlite.busy_timeout' => 5000]);
        config(['database.connections.sqlite.wal_mode' => false]); // Disable WAL to prevent locking
    }

    /**
     * Cleanup the test environment.
     */
    protected function tearDown(): void
    {
        $this->beforeApplicationDestroyed(function () {
            // Close database connections before cleanup
            $this->closeDatabaseConnections();
        });

        parent::tearDown();
    }

    /**
     * Run database migrations for Dusk tests.
     */
    protected function runMigrations(): void
    {
        $databasePath = static::$databaseFile;

        // Remove existing database file to ensure clean state
        if (file_exists($databasePath)) {
            unlink($databasePath);
        }

        // Remove WAL and SHM files if they exist
        $walFile = $databasePath.'-wal';
        $shmFile = $databasePath.'-shm';
        if (file_exists($walFile)) {
            unlink($walFile);
        }
        if (file_exists($shmFile)) {
            unlink($shmFile);
        }

        // Create fresh database file
        $directory = dirname($databasePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        touch($databasePath);
        chmod($databasePath, 0666);

        // Clear any existing database connections
        DB::purge('sqlite');

        // Wait for database to be available
        $this->waitForDatabase();

        // Run migrations
        $this->artisan('migrate:fresh', [
            '--database' => 'sqlite',
            '--force' => true,
        ]);
    }

    /**
     * Begin a database transaction.
     */
    protected function beginDatabaseTransaction(): void
    {
        DB::beginTransaction();
    }

    /**
     * Rollback the current database transaction.
     */
    protected function rollbackDatabaseTransaction(): void
    {
        DB::rollBack();
    }

    /**
     * Close database connections.
     */
    protected function closeDatabaseConnections(): void
    {
        try {
            DB::connection('sqlite')->disconnect();
        } catch (\Exception) {
            // Ignore disconnection errors
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
                DB::purge('sqlite');
                DB::connection('sqlite')->getPdo();

                // Test with a simple query to ensure database is writable
                DB::connection('sqlite')->statement('SELECT 1');
                break;
            } catch (\Exception $e) {
                if ($i === $maxRetries - 1) {
                    throw new \Exception("Failed to connect to database after {$maxRetries} attempts: ".$e->getMessage());
                }
                usleep($retryDelay * 1000);
            }
        }
    }

    /**
     * Create and authenticate a user for testing.
     */
    protected function createAuthenticatedUser(?Organization $organization = null): User
    {
        $this->organization = $organization ?: Organization::factory()->create();

        $this->user = User::factory()->create();

        $this->organization->users()->attach($this->user->id, [
            'roles' => json_encode(['admin']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Set current organization for user
        $this->user->current_organization_id = $this->organization->id;
        $this->user->save();

        $this->browse(function ($browser) {
            $browser->loginAs($this->user);
        });

        return $this->user;
    }

    /**
     * Switch to a specific organization context.
     */
    protected function switchToOrganization(Organization $organization): void
    {
        $this->organization = $organization;

        $this->browse(function ($browser) use ($organization) {
            $browser->visit("/switch-organization/{$organization->id}")
                ->waitForLocation('/', 10);
        });
    }

    /**
     * Get current organization for testing.
     */
    protected function getCurrentOrganization(): Organization
    {
        if (! $this->organization) {
            $this->organization = Organization::factory()->create();
        }

        return $this->organization;
    }

    /**
     * Create browser with organization context.
     */
    protected function createBrowserWithOrganization(callable $callback, ?Organization $organization = null): void
    {
        $org = $organization ?: $this->getCurrentOrganization();
        $user = $this->createAuthenticatedUser($org);

        $this->browse(function ($browser) use ($callback, $org, $user) {
            $browser->loginAs($user)
                ->visit('/')
                ->pause(2000);

            $callback($browser, $org, $user);
        });
    }

    /**
     * Take a screenshot on failure for debugging.
     * Note: Dusk automatically handles screenshots on test failure.
     */
    protected function takeScreenshotOnFailure(): void
    {
        // Dusk automatically captures screenshots on failure
        // Screenshots are stored in tests/Browser/screenshots
    }

    /**
     * Store console output on failure for debugging.
     * Note: Dusk automatically handles console logs on test failure.
     */
    protected function storeConsoleOutputOnFailure(): void
    {
        // Dusk automatically captures console logs on failure
        // Console logs are stored in tests/Browser/console
    }
}
