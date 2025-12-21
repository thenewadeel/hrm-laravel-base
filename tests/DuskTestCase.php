<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Browser\Concerns\HandlesDatabaseIsolation;

abstract class DuskTestCase extends BaseTestCase
{
    use DatabaseTransactions;
    use HandlesDatabaseIsolation;

    /**
     * The organization instance for multi-tenant testing.
     */
    protected ?Organization $organization = null;

    /**
     * The user instance for authentication testing.
     */
    protected ?User $user = null;

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
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--disable-dev-shm-usage',
            '--no-sandbox',
            '--disable-web-security',
            '--allow-running-insecure-content',
            '--disable-extensions',
            '--disable-plugins',
            '--disable-images', // Speed up tests
            // '--disable-javascript', // REMOVED - JS needed for Dusk tests
            '--disable-features=VizDisplayCompositor', // Prevent GPU issues
            '--remote-debugging-port=9222', // Allow debugging
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://127.0.0.1:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set proper environment for Dusk tests BEFORE app initialization
        $this->setUpDuskEnvironment();

        // For batched testing, use shared database to reduce overhead
        // Individual tests will handle their own isolation via transactions
        $this->configureSharedDatabase();

        // Ensure database file exists and is writable
        $dbFile = storage_path('testing_dusk.sqlite');
        if (! file_exists($dbFile)) {
            file_put_contents($dbFile, '');
            chmod($dbFile, 0666);
        }

        // Wait for database to be available before running migrations
        $this->waitForDatabase();

        // Only run migrations if they haven't been run yet
        try {
            \Illuminate\Support\Facades\DB::connection('sqlite')->table('migrations')->first();
        } catch (\Exception $e) {
            $this->artisan('migrate:fresh', [
                '--database' => 'sqlite',
                '--force' => true,
            ]);
        }

        // Begin database transaction for test isolation
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
    }

    /**
     * Clean up the test environment.
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
     * Configure database for shared batch testing.
     */
    protected function configureSharedDatabase(): void
    {
        $dbFile = storage_path('testing_dusk.sqlite');

        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => $dbFile]);
        config(['database.connections.sqlite.foreign_key_constraints' => true]);
        config(['database.connections.sqlite.busy_timeout' => 5000]);
        config(['database.connections.sqlite.wal_mode' => false]); // Disable WAL to prevent locking
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
     * Get the current organization for testing.
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
                ->waitForText($org->name, 10);

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
