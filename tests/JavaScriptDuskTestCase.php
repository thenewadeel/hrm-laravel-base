<?php

namespace Tests;

use App\Models\Organization;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tests\Browser\Concerns\HandlesDatabaseIsolation;

abstract class JavaScriptDuskTestCase extends BaseTestCase
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
            // Ensure ChromeDriver is running with proper settings
            static::startChromeDriver();
        }
    }

    /**
     * Create the RemoteWebDriver instance with JavaScript support.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-plugins',
            // Keep images enabled for JS tests
            '--disable-web-security',
            '--allow-running-insecure-content',
            '--window-size=1920,1080',
            '--disable-features=VizDisplayCompositor',
            '--disable-software-rasterizer',
            '--disable-background-timer-throttling',
            '--disable-renderer-backgrounding',
            '--disable-backgrounding-occluded-windows',
            '--disable-ipc-flooding-protection',
            '--enable-automation', // Allow automation
            '--disable-infobars', // Remove infobars
            '--start-maximized', // Start maximized
            '--disable-notifications', // Disable notifications
            '--disable-popup-blocking', // Allow popups if needed
        ]);

        $capabilities = DesiredCapabilities::chrome();
        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
        $capabilities->setCapability('acceptInsecureCerts', true); // Allow self-signed certs

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            $capabilities,
            null,
            null,
            null,
            null,
            null,
            null,
            30000,  // connection timeout 30 seconds
            30000   // request timeout 30 seconds
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

        // Always recreate the database file
        // $dbFile = storage_path('testing_dusk.sqlite');

        // // Delete existing file if it exists
        // if (file_exists($dbFile)) {
        //     unlink($dbFile);
        // }

        // // Create new empty database file
        // file_put_contents($dbFile, '');
        // chmod($dbFile, 0666);

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
        } catch (\Exception) {
            $this->artisan('migrate:fresh', [
                '--database' => 'testing_sqlite',
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

        config(['database.default' => 'testing_sqlite']);
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
     * Wait for JavaScript to be ready with longer timeout for JS tests.
     */
    protected function waitForJavaScriptReady($browser, int $timeout = 10): void
    {
        $browser->waitUsing($timeout, 500, function () use ($browser) {
            return $browser->script('return document.readyState === "complete"')[0] ?? false;
        });
    }

    /**
     * Wait for Livewire to be available.
     */
    protected function waitForLivewire($browser, int $timeout = 10): void
    {
        $browser->waitUsing($timeout, 500, function () use ($browser) {
            return $browser->script('return window.Livewire !== undefined')[0] ?? false;
        });
    }
}
