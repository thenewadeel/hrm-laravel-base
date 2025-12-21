<?php

namespace Tests\Browser;

use App\Models\Organization;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            // Set ChromeDriver path to use vendor binary
            $projectRoot = dirname(dirname(dirname(__DIR__)));
            $chromedriverPath = $projectRoot.'/vendor/laravel/dusk/bin/chromedriver-linux';

            if (file_exists($chromedriverPath)) {
                $_ENV['DUSK_DRIVER_PATH'] = $chromedriverPath;

                // Ensure binary is executable
                if (! is_executable($chromedriverPath)) {
                    chmod($chromedriverPath, 0755);
                }
            }

            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create RemoteWebDriver instance with JavaScript enabled.
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
            '--disable-images', // Speed up tests but keep JS enabled
            '--disable-gpu',
            '--headless=new',
            '--disable-background-timer-throttling',
            '--disable-backgrounding-occluded-windows',
            '--disable-renderer-backgrounding',
            '--disable-features=TranslateUI,BlinkGenPropertyTrees',
            '--disable-ipc-flooding-protection',
            '--disable-logging',
            '--disable-permissions-api',
            '--disable-notifications',
            '--disable-default-apps',
            '--no-first-run',
            '--no-default-browser-check',
            '--disable-extensions-except',
            '--disable-component-extensions-with-background-pages',
            '--disable-background-networking',
            '--disable-sync',
            '--metrics-recording-only',
            '--no-report-upload',
            '--disable-domain-reliability',
            // Note: --disable-javascript is removed to enable JavaScript testing
        ])->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Setup test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Use simple database approach for now
        $dbFile = storage_path('testing_dusk.sqlite');

        // Set up database configuration with optimizations
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => $dbFile]);
        config(['database.connections.sqlite.foreign_key_constraints' => true]);
        config(['database.connections.sqlite.busy_timeout' => 1000]); // Reduced timeout
        config(['database.connections.sqlite.synchronous' => 'OFF']); // Faster but less safe
        config(['database.connections.sqlite.journal_mode' => 'MEMORY']); // In-memory journal
        config(['database.connections.sqlite.cache' => 'shared']); // Enable caching

        // Clean up any existing test database
        if (file_exists($dbFile)) {
            unlink($dbFile);
        }

        // Create empty database file with proper permissions
        file_put_contents($dbFile, '');
        chmod($dbFile, 0666);

        // Set proper environment for Dusk tests
        $this->setUpDuskEnvironment();

        // Generate proper app key if not set (only once)
        if (empty(config('app.key'))) {
            $this->artisan('key:generate', ['--force' => true]);
        }

        // Run migrations to ensure all tables are created (only once per test run)
        if (!static::$migrated) {
            $this->artisan('migrate:fresh', [
                '--database' => 'sqlite',
                '--force' => true,
                '--seed' => false, // Skip seeding for performance
            ]);
            static::$migrated = true;
        }

        // Begin database transaction for test isolation
        $this->beginDatabaseTransaction();
    }

    /**
     * Set up Dusk environment configuration.
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
        putenv('APP_NAME=HRM-Base');
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
