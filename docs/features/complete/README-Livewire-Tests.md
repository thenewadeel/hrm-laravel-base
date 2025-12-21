# Livewire Component Interaction Tests

This directory contains comprehensive browser tests for Livewire component interactions across the HRM system.

## Test Infrastructure

### Core Files

1. **`LivewireInteractionTest.php`** - Main test file containing comprehensive test scenarios
2. **`Concerns/HandlesLivewireTesting.php`** - Livewire-specific testing helpers and utilities
3. **`Assertions/LivewireAssertions.php`** - Custom assertions for Livewire components
4. **`LivewireTestRunner.php`** - Test runner for generating comprehensive reports

### Test Coverage Areas

#### 1. Component Method Calls from Frontend
- ✅ Method existence verification
- ✅ Direct method invocation via JavaScript
- ✅ Parameter passing and return values
- ✅ Error handling for missing methods

#### 2. Alpine.js + Livewire Integration
- ✅ Component data accessibility from Alpine
- ✅ Reactive data binding
- ✅ Event system integration
- ✅ Lifecycle hook synchronization

#### 3. Component State Management
- ✅ Property setting and getting
- ✅ State synchronization
- ✅ Computed properties
- ✅ Reactive updates

#### 4. Form Submissions and Validation
- ✅ Form field binding with `wire:model`
- ✅ Validation error display
- ✅ Form submission handling
- ✅ Success/error notifications

#### 5. Modal Interactions
- ✅ Modal opening/closing
- ✅ Form rendering in modals
- ✅ Data persistence across modal states
- ✅ Backdrop click handling

#### 6. Real-time Updates and Polling
- ✅ Event dispatching
- ✅ Event listening
- ✅ Component refresh mechanisms
- ✅ Polling configuration

#### 7. Component Lifecycle Testing
- ✅ Mount hook execution
- ✅ Update hook triggers
- ✅ Component initialization
- ✅ Cleanup and destruction

## Tested Components

### FeeManager (`App\Livewire\Membership\FeeManager`)
- **Search functionality**: Real-time search with debouncing
- **Filtering**: Status, fee type, and custom filters
- **Sorting**: Column-based sorting with direction toggle
- **Form interactions**: Create fee and payment forms
- **Validation**: Client and server-side validation
- **Events**: Fee creation, payment processing, waiver events

### MemberManager (`App\Livewire\MemberManager`)
- **Search**: Member name and email search
- **Organization filtering**: Multi-tenant data isolation
- **Pagination**: Large dataset handling
- **Quick actions**: Common member operations

### Accounting Dashboard (`App\Livewire\Accounting\Dashboard`)
- **Data loading**: Service-based data fetching
- **Refresh functionality**: Manual data refresh
- **Statistics display**: Financial metrics and charts
- **Real-time updates**: Polling for live data

### NavigationMain (`App\Livewire\NavigationMain`)
- **Menu rendering**: Dynamic menu items
- **Active state**: Current page highlighting
- **Responsive behavior**: Mobile menu handling
- **Organization context**: Multi-tenant navigation

## Key Test Scenarios

### 1. Component Initialization
```php
$browser->waitForLivewireToLoad()
        ->assertLivewireComponentPresent('fee-manager')
        ->assertNoLivewireErrors()
        ->assertLivewirePropertyExists('fee-manager', 'search')
        ->assertLivewirePropertyEquals('fee-manager', 'search', '');
```

### 2. Form Interactions
```php
$browser->type('[wire\\:model="search"]', 'Test Fee')
        ->pause(500) // Wait for debouncing
        ->waitForLivewireUpdate('fee-manager')
        ->assertLivewirePropertyEquals('fee-manager', 'search', 'Test Fee');
```

### 3. Method Calls
```php
$this->callLivewireMethod($browser, 'fee-manager', 'showCreateFeeForm');
$browser->waitForLivewireUpdate('fee-manager')
        ->assertLivewirePropertyEquals('fee-manager', 'showCreateForm', true);
```

### 4. Event Handling
```php
$browser->assertLivewireEventDispatched('fee-created', ['feeId' => 123])
        ->assertLivewireListenerRegistered('fee-created');
```

### 5. Alpine.js Integration
```php
$browser->assertAlpineLivewireIntegration()
        ->assertAlpineLivewireReactivity('$wire.search', 'search');
```

## Running the Tests

### Individual Test Execution
```bash
# Run all Livewire interaction tests
php artisan dusk tests/Browser/LivewireInteractionTest.php

# Run specific test method
php artisan dusk --filter=test_fee_manager_component_basic_functionality
```

### Test Report Generation
```php
// Generate comprehensive test report
$results = LivewireTestRunner::runAllTests();
$report = LivewireTestRunner::generateReport($results);
file_put_contents('livewire-test-report.md', $report);
```

### Debug Mode
```php
// Enable debug mode for detailed logging
$browser->debug('livewire-test-debug')
        ->screenshot('livewire-component-state');
```

## Common Issues Detected

### 1. Missing Method Errors
- **Symptom**: `Method [method] does not exist on component`
- **Detection**: `assertLivewireMethodExists()` checks
- **Fix**: Ensure all called methods are defined in component class

### 2. Undefined Variable Errors
- **Symptom**: `Undefined property [property] on component`
- **Detection**: `assertLivewirePropertyExists()` validation
- **Fix**: Declare all public properties used in views

### 3. Component State Synchronization
- **Symptom**: Frontend state not matching backend state
- **Detection**: `assertLivewireStateSynchronized()` checks
- **Fix**: Ensure proper `wire:model` usage and event handling

### 4. Alpine.js Integration Issues
- **Symptom**: Alpine cannot access Livewire properties
- **Detection**: `assertAlpineLivewireIntegration()` tests
- **Fix**: Ensure proper component initialization and data exposure

### 5. Performance Issues
- **Symptom**: Slow component response times
- **Detection**: Response time measurements in tests
- **Fix**: Optimize queries, implement caching, reduce payload size

## Test Data Setup

### Required Test Data
```php
protected function setUp(): void
{
    parent::setUp();
    
    $this->organization = Organization::factory()->create();
    $this->adminUser = User::factory()->create(['email_verified_at' => now()]);
    $this->regularUser = User::factory()->create(['email_verified_at' => now()]);
    
    // Attach users to organization with roles
    $this->organization->users()->attach($this->adminUser->id, ['roles' => 'admin']);
    $this->organization->users()->attach($this->regularUser->id, ['roles' => 'member']);
}
```

### Factory Usage
```php
$member = Member::factory()->create(['organization_id' => $this->organization->id]);
$fee = MemberFee::factory()->create([
    'member_id' => $member->id,
    'organization_id' => $this->organization->id,
    'amount' => 100.00,
]);
```

## Performance Benchmarks

### Expected Response Times
- **Component initialization**: < 500ms
- **Search/filter operations**: < 1000ms
- **Form submissions**: < 2000ms
- **Modal interactions**: < 300ms

### Memory Usage
- **Component memory footprint**: < 5MB
- **Large dataset handling**: < 50MB for 1000+ records

## Security Testing

### Authorization Checks
```php
// Test regular user cannot access admin features
$browser->loginAs($this->regularUser)
        ->visit('/membership/fees')
        ->assertDontSee('Create New Fee'); // Admin-only feature
```

### Data Isolation
```php
// Test multi-tenant data isolation
$browser->loginAs($this->userFromOrgA)
        ->visit('/membership/fees')
        ->assertDontSee($feeFromOrgB->description);
```

## Accessibility Testing

### Keyboard Navigation
```php
$browser->keys('[wire\\:model="search"]', ['tab'])
        ->assertFocused('[wire\\:model="status"]');
```

### ARIA Attributes
```php
$browser->assertPresent('[wire\\:model][aria-label]')
        ->assertPresent('button[wire\\:click][aria-expanded]');
```

## Continuous Integration

### GitHub Actions Integration
```yaml
- name: Run Livewire Browser Tests
  run: |
    php artisan dusk --env=testing
    php artisan tests:generate-livewire-report
```

### Test Reporting
- **Coverage metrics**: Component method and property coverage
- **Performance tracking**: Response time trends
- **Error monitoring**: JavaScript and PHP error detection
- **Accessibility compliance**: WCAG 2.1 AA standards

## Best Practices

### Test Organization
1. **Group related tests**: Use descriptive test method names
2. **Reuse test data**: Share setup across test methods
3. **Isolate tests**: Ensure tests don't depend on each other
4. **Clean up**: Reset state between tests

### Performance Optimization
1. **Use selective waits**: Only wait for necessary elements
2. **Avoid unnecessary pauses**: Use `waitFor` instead of `pause`
3. **Batch operations**: Group multiple assertions
4. **Parallel execution**: Run tests concurrently when possible

### Maintenance
1. **Update selectors**: Keep selectors in sync with UI changes
2. **Review test data**: Ensure factories produce realistic data
3. **Monitor flaky tests**: Identify and fix timing-related issues
4. **Regular updates**: Keep tests aligned with feature changes

## Troubleshooting

### Common Test Failures

#### 1. Component Not Found
```
Livewire component 'fee-manager' not found
```
**Solution**: Check component registration and route configuration

#### 2. Method Not Found
```
Method 'createFee' does not exist
```
**Solution**: Verify method exists and is public in component class

#### 3. Property Not Found
```
Undefined property 'search'
```
**Solution**: Declare property as public in component class

#### 4. Timeout Errors
```
Wait timed out after 10000ms
```
**Solution**: Increase timeout or check for loading states

#### 5. JavaScript Errors
```
Livewire JavaScript errors detected
```
**Solution**: Check browser console for specific errors

### Debug Tools

#### Browser Console
```javascript
// Check Livewire components
console.log(window.Livewire.components.componentsArray);

// Check component data
const component = window.Livewire.find('component-id');
console.log(component.$wire);
```

#### Laravel Debugging
```php
// Add logging to component methods
Log::info('Component method called', ['data' => $data]);

// Check component state
dd($this->all());
```

This comprehensive test suite ensures robust Livewire component interactions, catches integration issues early, and maintains high code quality across the HRM system.