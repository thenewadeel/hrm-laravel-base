# Dashboard Browser Tests

This directory contains comprehensive browser tests for the dashboard system, focusing on Alpine.js functionality, search capabilities, navigation, and multi-tenant data isolation.

## Test Files

### Main Test File
- **`DashboardTest.php`** - Main dashboard test suite covering all major functionality

### Supporting Traits
- **`HandlesAlpineTesting.php`** - Alpine.js specific testing utilities and assertions
- **`HandlesDrawerTesting.php`** - Drawer system testing functionality
- **`HandlesJavaScriptTesting.php`** - JavaScript error detection and performance testing
- **`HandlesMultiTenantTesting.php`** - Multi-tenant data isolation testing
- **`HandlesSearchTesting.php`** - Search functionality testing

### Utilities
- **`DashboardTestDataFactory.php`** - Test data creation and management utilities
- **`DashboardAssertions.php`** - Custom assertions for dashboard testing

## Test Coverage

### Alpine.js Testing
- ✅ Alpine.js data binding verification
- ✅ Component initialization checks
- ✅ Reactivity testing
- ✅ Method existence and functionality
- ✅ Directive testing (x-show, x-text, x-model, etc.)
- ✅ Event handling verification

### Dashboard Functionality
- ✅ Page loading and statistics display
- ✅ Quick action links functionality
- ✅ Low stock alerts
- ✅ Recent transactions display
- ✅ Store listings
- ✅ Organization context verification

### Navigation & UI
- ✅ Mobile menu toggle
- ✅ Responsive design testing
- ✅ Theme switching
- ✅ Navigation menu functionality
- ✅ User menu testing
- ✅ Drawer system (left, right, top, bottom)

### Search Functionality
- ✅ Search input existence and functionality
- ✅ Alpine.js search data binding
- ✅ Search variable definition verification
- ✅ Input event handling
- ✅ Debouncing functionality
- ✅ Special characters and Unicode support
- ✅ Performance testing
- ✅ Accessibility compliance

### Multi-Tenant Testing
- ✅ Data isolation between organizations
- ✅ Organization switching
- ✅ Role-based access control
- ✅ Data scoping verification
- ✅ Cross-organization data leakage prevention
- ✅ Concurrent session testing

### JavaScript & Performance
- ✅ JavaScript error detection
- ✅ Console output monitoring
- ✅ Performance measurement
- ✅ Memory usage checking
- ✅ Module loading verification
- ✅ Async operation testing

### Accessibility
- ✅ ARIA attributes verification
- ✅ Keyboard navigation testing
- ✅ Screen reader compatibility
- ✅ Focus management
- ✅ Heading structure validation

## Running Tests

### Individual Test
```bash
php artisan dusk tests/Browser/DashboardTest.php
```

### Specific Test Method
```bash
php artisan dusk --filter=test_dashboard_loads_with_alpine_data
```

### All Dashboard Tests
```bash
php artisan dusk --filter=DashboardTest
```

### With Debugging
```bash
php artisan dusk --filter=DashboardTest --debug
```

## Test Data Setup

The tests use the `DashboardTestDataFactory` to create realistic test data:

```php
// Create basic test data
$data = DashboardTestDataFactory::createForOrganization($organization);

// Create scenario-specific data
$data = DashboardTestDataFactory::createScenario('low_stock_heavy', $organization);

// Create multi-tenant data
$data = DashboardTestDataFactory::createMultiTenantData(3);
```

## Custom Assertions

The test suite includes custom assertions for dashboard-specific functionality:

```php
// Assert dashboard statistics
$this->assertDashboardStats($browser, [
    'stores' => 3,
    'items' => 15,
    'low_stock' => 2,
]);

// Assert Alpine.js components
$this->assertAlpineComponentsInitialized($browser);

// Assert data isolation
$this->assertDataIsolation($browser, 'Org 1', 'Org 2');

// Assert no JavaScript errors
$this->assertNoJavaScriptErrors($browser);
```

## Alpine.js Testing

### Data Property Testing
```php
$this->assertAlpineData($browser, 'test', 'working');
$this->assertAlpineData($browser, 'drawers.left', false);
```

### Method Testing
```php
$this->assertAlpineMethodExists($browser, 'toggleDrawer');
$this->callAlpineMethod($browser, 'toggleDrawer', ['left']);
```

### Reactivity Testing
```php
$this->assertAlpineReactivity($browser, 'mobileMenuOpen', true);
```

## Drawer System Testing

### Basic Drawer Operations
```php
$this->toggleDrawer($browser, 'left');
$this->assertDrawerState($browser, 'left', true);
$this->assertDrawerVisible($browser, 'left');
$this->closeDrawer($browser, 'left');
```

### Advanced Drawer Testing
```php
$this->testDrawerKeyboardNavigation($browser, 'right');
$this->assertDrawerAccessibility($browser, 'top');
$this->testDrawerBackdropClick($browser, 'bottom');
```

## Search Testing

### Basic Search Functionality
```php
$this->assertSearchVariableDefined($browser);
$this->testSearchFunctionality($browser, 'test term');
$this->testAlpineSearchBinding($browser);
```

### Advanced Search Testing
```php
$this->testSearchSpecialCharacters($browser);
$this->testSearchUnicode($browser);
$this->testSearchPerformance($browser);
$this->testSearchAccessibility($browser);
```

## Multi-Tenant Testing

### Data Isolation
```php
$this->testDashboardDataIsolation();
$this->assertDataIsolation($browser, $currentOrg, $otherOrg);
```

### Organization Switching
```php
$this->testOrganizationSwitching();
$this->switchOrganization($browser, $newOrganization);
```

## JavaScript Error Detection

The test suite includes comprehensive JavaScript error detection:

```php
// Check for any JavaScript errors
$errors = $this->checkForJavaScriptErrors($browser);
$this->assertEmpty($errors);

// Monitor console output
$consoleLogs = $this->getConsoleOutput($browser);

// Test specific error scenarios
$this->testJavaScriptErrorHandling($browser);
```

## Performance Testing

### Page Load Performance
```php
$this->assertPagePerformance($browser, 5.0); // Max 5 seconds
```

### JavaScript Performance
```php
$performance = $this->measureJavaScriptPerformance($browser, $script);
$this->assertLessThan(1.0, $performance['executionTime']);
```

## Accessibility Testing

### Basic Accessibility
```php
$this->assertAccessibilityStandards($browser);
```

### Specific Accessibility Features
```php
$this->testSearchAccessibility($browser);
$this->assertDrawerAccessibility($browser, 'left');
```

## Debugging

### Screenshots
```php
$this->debug($browser, 'dashboard-debug');
$browser->screenshot('dashboard-state');
```

### Console Output
```php
$browser->dump($this->getConsoleOutput($browser));
$browser->console();
```

### JavaScript Execution
```php
$result = $this->executeScript($browser, "return document.title;");
$browser->script("console.log('Debug info');");
```

## Best Practices

1. **Always wait for JavaScript** before making assertions
2. **Use custom assertions** for dashboard-specific functionality
3. **Test both positive and negative scenarios**
4. **Verify data isolation** in multi-tenant scenarios
5. **Check for JavaScript errors** after each interaction
6. **Test responsive design** at different viewport sizes
7. **Verify accessibility** compliance
8. **Use the data factory** for consistent test data
9. **Clean up test data** to avoid interference between tests
10. **Use descriptive test names** that explain what is being tested

## Troubleshooting

### Common Issues

1. **Alpine.js not initialized**
   - Ensure `waitForAlpine($this)` is called
   - Check that elements have proper `x-data` attributes

2. **JavaScript errors**
   - Use `checkForJavaScriptErrors()` to identify issues
   - Check browser console for detailed error messages

3. **Drawer not working**
   - Verify drawer toggle buttons exist
   - Check Alpine.js drawer methods are defined

4. **Search not defined errors**
   - Use `assertSearchVariableDefined()` to verify setup
   - Check Alpine.js search bindings

5. **Data isolation issues**
   - Verify organization context is properly set
   - Check database queries include organization scoping

### Debug Mode

Run tests with debug mode for more detailed output:

```bash
php artisan dusk --filter=DashboardTest --debug
```

This will provide additional information about:
- JavaScript console output
- Network requests
- Performance metrics
- Detailed error messages