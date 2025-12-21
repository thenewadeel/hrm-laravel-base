# Laravel Dusk Browser Testing

This document explains how to set up and use Laravel Dusk for browser testing in the HRM Laravel Base ERP system.

## Installation

Dusk is already installed and configured. The following components are set up:

- Laravel Dusk package (`laravel/dusk`)
- ChromeDriver binary
- Browser test directory structure (`tests/Browser/`)
- Dusk-specific environment configuration (`.env.dusk`)
- Base test classes and traits
- CI/CD pipeline configuration

## Running Tests

### Local Development

```bash
# Run all browser tests
php artisan dusk

# Run specific test file
php artisan dusk tests/Browser/DuskInstallationTest.php

# Run specific test method
php artisan dusk --filter=test_dusk_installation_works

# Run with verbose output
php artisan dusk --verbose

# Run in headed mode (show browser window)
php artisan dusk --no-headless

# Run with specific environment
php artisan dusk --env=dusk
```

### Using Laravel Sail

```bash
# Start Sail with Selenium service
sail up -d

# Run Dusk tests
sail dusk
```

## Test Structure

```
tests/Browser/
├── BaseBrowserTest.php          # Base class with common functionality
├── DuskInstallationTest.php     # Verification tests
├── Traits/
│   └── BrowserTestSetup.php     # Setup helpers for multi-tenant testing
├── Pages/                       # Page object models
├── screenshots/                 # Screenshots on test failures
├── console/                     # Console logs on test failures
└── source/                      # Page source on test failures
```

## Key Features

### Multi-Tenant Support

All browser tests support the multi-tenant architecture:

```php
// Setup organization with user
$organization = $this->setupOrganizationWithAccounting($browser);

// Switch between organizations
$this->switchOrganizationInBrowser($browser, $targetOrganization);

// Verify organization context
$this->assertOrganizationContext($browser, $organization);
```

### Authentication Helpers

```php
// Login as admin
$user = $this->loginAsAdmin($browser);

// Login as regular user
$user = $this->loginAsUser($browser);

// Create authenticated user with specific role
$scenario = $this->setupUserWithRole($browser, 'member');
```

### Module Navigation

```php
// Navigate to specific modules
$this->navigateToAccounting($browser);
$this->navigateToHRM($browser);
$this->navigateToInventory($browser);
$this->navigateToOrganizationSettings($browser);
```

### Livewire Testing

```php
// Wait for Livewire components
$this->waitForLivewireComponent($browser, 'dashboard');

// Interact with Livewire forms
$browser->click('@create-voucher-button')
       ->waitForLivewireComponent('voucher-form');
```

### Responsive Design Testing

```php
// Test at different screen sizes
$this->testResponsiveDesign($browser, function (Browser $browser, string $device) {
    // Your test logic here
});
```

## Environment Configuration

### .env.dusk

The Dusk environment file includes:

- `APP_ENV=dusk` - Specific environment for browser tests
- `APP_URL=http://127.0.0.1:8000` - URL for the test server
- `MAIL_MAILER=array` - Prevents actual email sending
- `DUSK_DRIVER_URL=http://localhost:9515` - ChromeDriver URL

### Database Configuration

Browser tests use database transactions to ensure isolation:

```php
use DatabaseTransactions;

// Each test runs in a transaction
// Database is rolled back after each test
```

## Writing Browser Tests

### Basic Test Structure

```php
<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\Browser\BaseBrowserTest;
use Tests\Browser\Traits\BrowserTestSetup;

class FeatureTest extends BaseBrowserTest
{
    use BrowserTestSetup;

    public function test_example(): void
    {
        $this->browse(function (Browser $browser) {
            $this->setupOrganizationWithAccounting($browser);
            
            $browser->visit('/accounts')
                   ->assertSee('Chart of Accounts')
                   ->clickLink('Create Account')
                   ->waitFor('.account-form', 10)
                   ->type('name', 'Test Account')
                   ->select('type', 'asset')
                   ->click('button[type="submit"]')
                   ->waitForText('Account created successfully', 10);
        });
    }
}
```

### Common Assertions

```php
// Page assertions
$browser->assertTitleContains('Dashboard')
       ->assertPathIs('/dashboard')
       ->assertSee('Welcome')
       ->assertDontSee('Error');

// Form assertions
$browser->assertPresent('form')
       ->assertEnabled('submit-button')
       ->assertSelected('type', 'asset');

// Table assertions
$browser->assertTableContains('Test Account')
       ->assertTableDoesNotContain('Deleted Account');

// Livewire assertions
$browser->waitForLivewireComponent('user-list')
       ->assertPresent('[wire\\:id*="user-list"]');
```

### Form Interactions

```php
// Fill forms
$this->fillForm($browser, [
    'name' => 'Test Name',
    'email' => 'test@example.com',
]);

// Select options
$this->selectOption($browser, 'role', 'admin');

// File uploads
$this->uploadFile($browser, 'avatar', '/path/to/file.jpg');

// Handle validation errors
$this->assertValidationErrors($browser, [
    'name' => 'Name is required',
    'email' => 'Email must be valid',
]);
```

### Debugging

```php
// Take screenshot
$browser->screenshot('test-debug');

// Pause execution
$browser->pause(5000);

// Debug helper
$this->debug($browser, 'debug-point');

// Store console output
$browser->storeConsoleLog();

// Store page source
$browser->storeSource('page-source');
```

## CI/CD Integration

### GitHub Actions

The `.github/workflows/dusk.yml` file provides:

- Ubuntu environment with Chrome
- MySQL database setup
- Parallel test execution
- Artifact upload for failures
- Multiple database testing

### Running in CI

```bash
# Install ChromeDriver
php artisan dusk:chrome-driver --detect

# Start ChromeDriver
./vendor/laravel/dusk/bin/chromedriver-linux --port=9515 &

# Start Laravel server
php artisan serve --no-reload &

# Run tests
php artisan dusk --env=testing
```

## Best Practices

### 1. Test Organization

- Group related tests in descriptive classes
- Use descriptive test method names
- Leverage page object models for complex pages

### 2. Data Management

- Use factories for test data
- Clean up data in tearDown()
- Use database transactions for isolation

### 3. Performance

- Use headless mode in CI
- Disable images and CSS when not needed
- Reuse browser instances when possible

### 4. Reliability

- Use explicit waits instead of sleep()
- Handle async operations properly
- Test responsive design

### 5. Debugging

- Take screenshots on failures
- Capture console output
- Store page source for analysis

## Troubleshooting

### Common Issues

1. **ChromeDriver version mismatch**
   ```bash
   php artisan dusk:chrome-driver --detect
   ```

2. **Port conflicts**
   ```bash
   # Kill existing ChromeDriver processes
   pkill chromedriver
   
   # Use different port
   php artisan dusk --driver-url=http://localhost:9516
   ```

3. **Timing issues**
   ```php
   // Use explicit waits
   $browser->waitFor('.element', 10)
          ->waitForText('Content', 10);
   ```

4. **Authentication issues**
   ```php
   // Ensure user is properly authenticated
   $browser->loginAs($user)
          ->visit('/dashboard')
          ->assertAuthenticated();
   ```

### Debug Mode

Run tests with debugging enabled:

```bash
# Show browser window
php artisan dusk --no-headless

# Increase verbosity
php artisan dusk --verbose

# Run single test
php artisan dusk --filter=test_specific_method
```

## Performance Tips

1. **Reuse browser instances** - Use `$this->browse()` efficiently
2. **Minimize waits** - Use specific selectors instead of generic waits
3. **Disable features** - Turn off images, CSS, JavaScript when not needed
4. **Parallel execution** - Run tests in parallel when possible
5. **Database optimization** - Use SQLite for faster test execution

## Security Considerations

- Never run Dusk in production environments
- Use separate test database
- Sanitize test data
- Handle sensitive information carefully
- Use environment-specific configurations

## Integration with Existing Tests

Dusk tests complement existing unit and feature tests:

- **Unit tests**: Test individual components and business logic
- **Feature tests**: Test HTTP endpoints and application flow
- **Browser tests**: Test complete user workflows and JavaScript functionality

All three test types should be maintained for comprehensive coverage.