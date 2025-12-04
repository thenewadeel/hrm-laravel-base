# Membership Testing Infrastructure - Usage Guide

## 🎯 Introduction

This comprehensive usage guide provides detailed instructions for using, extending, and maintaining the Membership Testing Infrastructure. Whether you're a developer, QA engineer, or project manager, this guide will help you effectively leverage the testing framework.

---

## 🚀 Quick Start Guide

### 1. Running Tests for the First Time

#### Prerequisites
```bash
# Ensure dependencies are installed
composer install
npm install

# Ensure database is configured
cp .env.example .env
php artisan key:generate
php artisan migrate
```

#### Running All Membership Tests
```bash
# Run complete membership test suite
php artisan test --filter=Membership

# Run with coverage report
php artisan test --filter=Membership --coverage

# Run with detailed output
php artisan test --filter=Membership --verbose
```

#### Running Specific Test Categories
```bash
# Run only unit tests
php artisan test tests/Unit/Membership/

# Run only feature tests
php artisan test tests/Feature/Membership/

# Run specific test file
php artisan test tests/Feature/Membership/MultiTenantIsolationTest.php

# Run specific test method
php artisan test --filter=test_members_are_isolated_by_organization
```

### 2. Understanding Test Results

#### Test Output Interpretation
```
   PASS  Tests\Unit\Membership\MembershipServiceTest
  ✓ service creates member with proper relationships
  ✓ service handles subscription creation
  ✓ service integrates with accounting system

   FAIL  Tests\Feature\Membership\MultiTenantIsolationTest
  ✗ membership numbers are unique per organization
  → Expected 'MEM-000001' not to be 'MEM-000001'

Tests:    47 failed, 182 passed (594 assertions)
Time:     2.45 minutes
```

#### Coverage Report Analysis
```
Coverage Report:
├── Overall Coverage: 85%
├── MembershipService: 95%
├── Member Model: 92%
├── MemberSubscription: 88%
└── MemberFee: 90%
```

---

## 🏗️ Using the SetupMembership Trait

### 1. Basic Usage

#### Including the Trait
```php
<?php

use Tests\Traits\SetupMembership;

uses(SetupMembership::class);

beforeEach(function () {
    $this->setupMembershipManagement();
});

test('example test with membership data', function () {
    // All membership test data is now available
    expect($this->testMember)->toBeInstanceOf(Member::class);
    expect($this->basicPlan)->toBeInstanceOf(SubscriptionPlan::class);
    expect($this->activeSubscription)->toBeInstanceOf(MemberSubscription::class);
});
```

#### Available Properties
```php
// Organization and Users
$this->membershipOrganization;    // Primary test organization
$this->membershipAdmin;          // Admin user with full access
$this->membershipStaff;           // Staff user with limited access

// Members
$this->testMember;               // Active member with family
$this->familyMember;             // Member on family plan
$this->expiredMember;            // Expired member
$this->suspendedMember;         // Suspended member

// Subscription Plans
$this->basicPlan;                // Individual monthly plan
$this->premiumPlan;              // Individual yearly plan
$this->familyPlan;               // Family monthly plan

// Subscriptions
$this->activeSubscription;       // Active subscription
$this->expiredSubscription;      // Expired subscription

// Fees
$this->pendingFee;               // Pending payment fee
$this->paidFee;                  // Paid fee
$this->overdueFee;               // Overdue fee
```

### 2. Authentication Helper Methods

#### Acting as Different User Types
```php
test('admin can access all membership features', function () {
    $this->actingAsMembershipAdmin();
    
    $response = $this->get('/membership/dashboard');
    $response->assertSuccessful();
});

test('staff has limited access', function () {
    $this->actingAsMembershipStaff();
    
    $response = $this->delete('/membership/members/1');
    $response->assertForbidden();
});
```

#### Getting User Instances
```php
test('custom authentication scenarios', function () {
    $admin = $this->getMembershipAdmin();
    $staff = $this->getMembershipStaff();
    
    expect($admin->hasRole('organization_admin'))->toBeTrue();
    expect($staff->hasRole('membership_staff'))->toBeTrue();
});
```

### 3. Advanced Helper Methods

#### Creating Members with Subscriptions
```php
test('custom member with subscription', function () {
    [$member, $subscription] = $this->createMemberWithSubscription(
        memberOverrides: [
            'first_name' => 'Custom',
            'last_name' => 'Member',
            'email' => 'custom@test.com',
        ],
        subscriptionOverrides: [
            'status' => 'trial',
            'trial_end_date' => now()->addDays(14),
        ]
    );
    
    expect($member->first_name)->toBe('Custom');
    expect($subscription->status)->toBe('trial');
});
```

#### Creating Members with Family
```php
test('member with multiple family members', function () {
    [$member, $familyMembers] = $this->createMemberWithFamily(
        familyCount: 3,
        memberOverrides: [
            'first_name' => 'Parent',
            'last_name' => 'Member',
        ],
        familyOverrides: [
            'relationship' => 'child',
        ]
    );
    
    expect($familyMembers)->toHaveCount(3);
    expect($familyMembers->first()->relationship)->toBe('child');
});
```

#### Creating Members with Fees
```php
test('member with multiple fees', function () {
    [$member, $fees] = $this->createMemberWithFees(
        feeCount: 5,
        memberOverrides: [
            'email' => 'fees@test.com',
        ],
        feeOverrides: [
            'status' => 'pending',
            'amount' => 50.00,
        ]
    );
    
    expect($fees)->toHaveCount(5);
    expect($fees->sum('amount'))->toBe(250.00);
});
```

---

## 🧪 Writing Custom Tests

### 1. Unit Testing Patterns

#### Testing Model Methods
```php
test('member model methods work correctly', function () {
    $this->setupMembershipManagement();
    
    $member = $this->testMember;
    
    // Test status methods
    expect($member->isActive())->toBeTrue();
    expect($member->isExpired())->toBeFalse();
    expect($member->isSuspended())->toBeFalse();
    
    // Test relationship methods
    expect($member->subscriptions)->toHaveCount(1);
    expect($member->familyMembers)->toHaveCount(2);
    expect($member->fees)->toHaveCount(2); // pending + paid
});

test('subscription calculations are accurate', function () {
    $this->setupMembershipManagement();
    
    $subscription = $this->activeSubscription;
    
    expect($subscription->isPaid())->toBeTrue();
    expect($subscription->isExpired())->toBeFalse();
    expect($subscription->daysUntilExpiry())->toBeGreaterThan(0);
    expect($subscription->canAutoRenew())->toBeTrue();
});
```

#### Testing Service Methods
```php
test('membership service handles edge cases', function () {
    $this->setupMembershipManagement();
    $service = new MembershipService;
    
    // Test duplicate email handling
    $duplicateData = [
        'organization_id' => $this->membershipOrganization->id,
        'email' => $this->testMember->email, // Duplicate email
        'first_name' => 'Duplicate',
        'last_name' => 'Test',
    ];
    
    expect(fn() => $service->createMember($duplicateData))
        ->toThrow(ValidationException::class);
});
```

### 2. Feature Testing Patterns

#### Testing API Endpoints
```php
test('membership API endpoints work correctly', function () {
    $this->setupMembershipManagement();
    $this->actingAsMembershipAdmin();
    
    // Test member creation endpoint
    $memberData = [
        'first_name' => 'API',
        'last_name' => 'Test',
        'email' => 'api@test.com',
        'phone' => '1234567890',
    ];
    
    $response = $this->postJson('/api/membership/members', $memberData);
    
    $response->assertStatus(201)
        ->assertJsonStructure([
            'id',
            'membership_number',
            'first_name',
            'last_name',
            'email',
        ]);
});

test('API handles validation errors', function () {
    $this->setupMembershipManagement();
    $this->actingAsMembershipAdmin();
    
    $response = $this->postJson('/api/membership/members', [
        'first_name' => '', // Invalid
        'email' => 'invalid-email', // Invalid
    ]);
    
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'email']);
});
```

#### Testing Livewire Components
```php
test('membership dashboard component functions correctly', function () {
    $this->setupMembershipManagement();
    $this->actingAsMembershipAdmin();
    
    $component = Livewire::test(MembershipDashboard::class);
    
    // Test initial state
    $component->assertSee('Total Members')
        ->assertSee('Active Subscriptions')
        ->assertSee($this->testMember->first_name);
    
    // Test filtering
    $component->set('filter', 'active')
        ->assertSee($this->testMember->first_name)
        ->assertDontSee($this->expiredMember->first_name);
    
    // Test search
    $component->set('search', 'John Doe')
        ->assertSee($this->testMember->first_name)
        ->assertDontSee($this->familyMember->first_name);
});
```

### 3. Integration Testing Patterns

#### Testing Multi-Tenant Isolation
```php
test('data isolation works across organizations', function () {
    $this->setupMembershipManagement();
    
    // Create test data for multi-tenant testing
    $multiTenantData = $this->createMultiTenantTestData();
    
    // Test as org1 admin
    $this->actingAs($this->membershipAdmin)
        ->get('/membership/members')
        ->assertSee($multiTenantData['org1_member']->first_name)
        ->assertDontSee($multiTenantData['org2_member']->first_name);
    
    // Test membership number uniqueness
    expect($multiTenantData['org1_member']->membership_number)
        ->toBe('MEM-000001');
    expect($multiTenantData['org2_member']->membership_number)
        ->toBe('MEM-000001');
});
```

#### Testing Accounting Integration
```php
test('membership fees create proper accounting entries', function () {
    $this->setupMembershipManagement();
    
    // Create a paid fee
    $fee = MemberFee::factory()->create([
        'organization_id' => $this->membershipOrganization->id,
        'member_id' => $this->testMember->id,
        'amount' => 100.00,
        'paid_amount' => 100.00,
        'status' => 'paid',
        'payment_date' => now(),
    ]);
    
    // Verify accounting integration
    expect($fee->journalEntries)->toHaveCount(1);
    expect($fee->ledgerEntries)->toHaveCount(2);
    
    // Verify double-entry accounting
    $debitEntry = $fee->ledgerEntries()->where('entry_type', 'debit')->first();
    $creditEntry = $fee->ledgerEntries()->where('entry_type', 'credit')->first();
    
    expect($debitEntry->amount)->toBe(100.00);
    expect($creditEntry->amount)->toBe(100.00);
    expect($debitEntry->account_id)->not->toBe($creditEntry->account_id);
});
```

---

## 🔧 Extending the Testing Infrastructure

### 1. Adding New Test Scenarios

#### Creating Custom Test Data
```php
trait ExtendedMembershipSetup
{
    use SetupMembership;
    
    protected Member $vipMember;
    protected SubscriptionPlan $vipPlan;
    
    protected function setupExtendedMembership(): void
    {
        $this->setupMembershipManagement();
        $this->createVipMemberAndPlan();
    }
    
    protected function createVipMemberAndPlan(): void
    {
        $this->vipPlan = SubscriptionPlan::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'name' => 'VIP Plan',
            'plan_type' => 'individual',
            'amount' => 199.99,
            'features' => json_encode([
                'premium_access' => true,
                'personal_trainer' => true,
                'spa_access' => true,
            ]),
        ]);
        
        $this->vipMember = Member::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'first_name' => 'VIP',
            'last_name' => 'Member',
            'email' => 'vip@test.com',
            'membership_type' => 'vip',
        ]);
        
        MemberSubscription::factory()->create([
            'organization_id' => $this->membershipOrganization->id,
            'member_id' => $this->vipMember->id,
            'subscription_plan_id' => $this->vipPlan->id,
            'status' => 'active',
        ]);
    }
}
```

#### Adding New Test Categories
```php
// tests/Feature/Membership/VipMembershipTest.php
<?php

use Tests\Traits\ExtendedMembershipSetup;
use App\Services\Membership\VipMembershipService;

uses(ExtendedMembershipSetup::class);

describe('VIP Membership Features', function () {
    beforeEach(function () {
        $this->setupExtendedMembership();
        $this->vipService = new VipMembershipService;
    });
    
    test('VIP members have premium access', function () {
        $hasAccess = $this->vipService->hasPremiumAccess($this->vipMember);
        
        expect($hasAccess)->toBeTrue();
    });
    
    test('VIP plan includes personal trainer', function () {
        $features = $this->vipPlan->features;
        
        expect($features['personal_trainer'])->toBeTrue();
    });
});
```

### 2. Creating Custom Assertions

#### Membership-Specific Assertions
```php
trait MembershipAssertions
{
    protected function assertMemberIsActive(Member $member): void
    {
        expect($member->status)->toBe('active');
        expect($member->expiry_date)->toBeGreaterThan(now());
        expect($member->isActive())->toBeTrue();
    }
    
    protected function assertSubscriptionIsPaid(MemberSubscription $subscription): void
    {
        expect($subscription->status)->toBe('paid');
        expect($subscription->paid_amount)->toBe($subscription->total_amount);
        expect($subscription->payment_date)->not->toBeNull();
    }
    
    protected function assertFeeIsOverdue(MemberFee $fee): void
    {
        expect($fee->status)->toBe('overdue');
        expect($fee->due_date)->toBeLessThan(now());
        expect($fee->paid_amount)->toBeLessThan($fee->amount);
    }
}

// Usage in tests
test('member status assertions work correctly', function () {
    $this->setupMembershipManagement();
    
    $this->assertMemberIsActive($this->testMember);
    $this->assertSubscriptionIsPaid($this->activeSubscription);
    $this->assertFeeIsOverdue($this->overdueFee);
});
```

### 3. Performance Testing Extensions

#### Load Testing Scenarios
```php
describe('Performance Testing', function () {
    test('handles concurrent member creation', function () {
        $this->setupMembershipManagement();
        
        $startTime = microtime(true);
        
        // Create 100 members concurrently
        $promises = [];
        for ($i = 0; $i < 100; $i++) {
            $promises[] = $this->async(function () use ($i) {
                return Member::factory()->create([
                    'organization_id' => $this->membershipOrganization->id,
                    'email' => "perf{$i}@test.com",
                ]);
            });
        }
        
        $results = Promise::all($promises);
        $endTime = microtime(true);
        
        expect($results)->toHaveCount(100);
        expect($endTime - $startTime)->toBeLessThan(10.0); // 10 second limit
    });
    
    test('member list query performance', function () {
        $this->setupMembershipManagement();
        
        // Create 1000 members
        Member::factory()->count(1000)->create([
            'organization_id' => $this->membershipOrganization->id,
        ]);
        
        $startTime = microtime(true);
        
        $members = Member::where('organization_id', $this->membershipOrganization->id)
            ->with(['subscriptions', 'fees'])
            ->paginate(50);
        
        $endTime = microtime(true);
        
        expect($members->total())->toBe(1000);
        expect($endTime - $startTime)->toBeLessThan(2.0); // 2 second limit
    });
});
```

---

## 🐛 Troubleshooting Common Issues

### 1. Test Failures

#### Database Connection Issues
```php
// Problem: Tests failing with database connection errors
// Solution: Ensure proper database configuration

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure we're using the test database
    config(['database.default' => 'sqlite']);
    config(['database.connections.sqlite.database' => ':memory:']);
});
```

#### Factory Relationship Issues
```php
// Problem: Foreign key constraint violations
// Solution: Ensure proper relationship setup

// Correct approach - create parent first
$member = Member::factory()->create([
    'organization_id' => $this->membershipOrganization->id,
]);

$subscription = MemberSubscription::factory()->create([
    'member_id' => $member->id, // Member must exist first
    'organization_id' => $member->organization_id,
]);

// Incorrect approach - may cause FK violations
$subscription = MemberSubscription::factory()->create([
    'member_id' => 999, // Non-existent member
]);
```

#### Authentication Issues
```php
// Problem: Tests failing with authentication errors
// Solution: Properly authenticate users

beforeEach(function () {
    $this->setupMembershipManagement();
    $this->actingAsMembershipAdmin(); // Authenticate before tests
});

test('authenticated test example', function () {
    // Test logic here - user is already authenticated
    $response = $this->get('/membership/dashboard');
    $response->assertSuccessful();
});
```

### 2. Performance Issues

#### Slow Test Execution
```php
// Problem: Tests running too slowly
// Solution: Use database transactions and eager loading

uses(RefreshDatabase::class);

// Use eager loading in tests
$members = Member::with(['subscriptions', 'fees'])->get();

// Avoid unnecessary database queries
$member = Member::factory()->create(); // Single creation
// Instead of
for ($i = 0; $i < 10; $i++) {
    Member::factory()->create(); // Multiple queries
}
```

#### Memory Issues
```php
// Problem: Tests running out of memory
// Solution: Clean up after tests

afterEach(function () {
    // Clear any cached data
    Cache::flush();
    
    // Reset any static properties
    SomeClass::resetStatic();
    
    // Clean up large collections
    unset($this->largeCollection);
});
```

### 3. Coverage Issues

#### Low Test Coverage
```php
// Problem: Coverage reports showing low coverage
// Solution: Add tests for uncovered code paths

test('edge case scenarios', function () {
    // Test boundary conditions
    $expiresToday = MemberSubscription::factory()->create([
        'end_date' => now()->endOfDay(),
    ]);
    expect($expiresToday->isExpired())->toBeFalse();
    
    $expiresTomorrow = MemberSubscription::factory()->create([
        'end_date' => now()->addDay()->startOfDay(),
    ]);
    expect($expiresTomorrow->isExpired())->toBeFalse();
    
    $expiredYesterday = MemberSubscription::factory()->create([
        'end_date' => now()->subDay()->endOfDay(),
    ]);
    expect($expiredYesterday->isExpired())->toBeTrue();
});
```

---

## 📊 Monitoring and Reporting

### 1. Test Execution Monitoring

#### Generating Test Reports
```bash
# Generate HTML coverage report
php artisan test --filter=Membership --coverage --coverage-html=reports/membership-coverage

# Generate JUnit XML for CI/CD
php artisan test --filter=Membership --log-junit=reports/membership-results.xml

# Generate performance profile
php artisan test --filter=Membership --profile
```

#### Custom Test Reporting
```php
class MembershipTestReporter
{
    public static function generateReport(): array
    {
        $results = [
            'total_tests' => 0,
            'passed' => 0,
            'failed' => 0,
            'coverage' => 0,
            'execution_time' => 0,
        ];
        
        // Analyze test results and generate report
        return $results;
    }
}
```

### 2. Continuous Integration

#### GitHub Actions Configuration
```yaml
name: Membership Tests

on:
  push:
    paths:
      - 'app/Models/Membership/**'
      - 'app/Services/Membership/**'
      - 'tests/**/Membership/**'

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
    - uses: actions/checkout@v3
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.4'
        coverage: xdebug
    - name: Install Dependencies
      run: composer install --no-progress
    - name: Run Tests
      run: |
        php artisan test --filter=Membership --coverage --coverage-clover=coverage.xml
    - name: Upload Coverage
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
        flags: membership
```

---

## 🎯 Best Practices

### 1. Test Organization

#### Naming Conventions
```php
// Good: Descriptive test names
test('membership_service_creates_member_with_unique_number', function () {
    // Test implementation
});

test('subscription_renewal_sends_notification_email', function () {
    // Test implementation
});

// Bad: Vague test names
test('test_member_creation', function () {
    // Test implementation
});

test('subscription_test', function () {
    // Test implementation
});
```

#### Test Structure
```php
describe('Feature Being Tested', function () {
    beforeEach(function () {
        // Setup common test data
        $this->setupMembershipManagement();
    });
    
    describe('Specific Scenario', function () {
        test('specific behavior under specific conditions', function () {
            // Arrange
            $member = $this->testMember;
            
            // Act
            $result = $member->someMethod();
            
            // Assert
            expect($result)->toBeTrue();
        });
    });
});
```

### 2. Data Management

#### Factory Usage
```php
// Good: Use factories with meaningful data
$member = Member::factory()->create([
    'organization_id' => $this->membershipOrganization->id,
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john.doe@test.com',
]);

// Bad: Manual data creation
$member = new Member;
$member->organization_id = $this->membershipOrganization->id;
$member->first_name = 'Test';
$member->save();
```

#### Test Data Isolation
```php
// Good: Use traits for reusable setup
uses(SetupMembership::class);

beforeEach(function () {
    $this->setupMembershipManagement();
});

// Bad: Duplicate setup in each test
test('test 1', function () {
    $org = Organization::factory()->create();
    $member = Member::factory()->create(['organization_id' => $org->id]);
    // ... test logic
});
```

### 3. Assertion Best Practices

#### Specific Assertions
```php
// Good: Use specific assertions
expect($member->status)->toBe('active');
expect($member->subscriptions)->toHaveCount(1);
expect($member->email)->toContain('@');

// Bad: Generic assertions
expect($member->status == 'active')->toBeTrue();
expect(count($member->subscriptions) == 1)->toBeTrue();
```

#### Relationship Testing
```php
// Good: Test relationships explicitly
expect($member->subscriptions)->first()->toBeInstanceOf(MemberSubscription::class);
expect($member->fees)->where('status', 'pending')->toHaveCount(1);

// Bad: Only test IDs
expect($member->subscription_id)->toBe(1);
```

---

## 📚 Additional Resources

### 1. Documentation References
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [Pest Testing Documentation](https://pestphp.com/docs)
- [Livewire Testing Documentation](https://livewire.laravel.com/docs/testing)

### 2. Related Test Files
- `tests/Traits/SetupMembership.php` - Core setup trait
- `tests/Traits/SetupOrganization.php` - Organization setup
- `tests/Traits/SetupTenancy.php` - Multi-tenant setup

### 3. Example Test Files
- `tests/Feature/Membership/MultiTenantIsolationTest.php` - Isolation testing
- `tests/Unit/Membership/MembershipServiceTest.php` - Service testing
- `tests/Feature/Membership/MembershipDashboardTest.php` - UI testing

---

## 🎯 Conclusion

This usage guide provides comprehensive instructions for effectively using and extending the Membership Testing Infrastructure. By following these guidelines and best practices, you can ensure high-quality, maintainable tests that provide confidence in the membership module's functionality and security.

### Key Takeaways
- ✅ **Quick Start**: Easy setup and execution of tests
- ✅ **Comprehensive Usage**: Detailed examples for all scenarios
- ✅ **Extension Guide**: Instructions for adding new functionality
- ✅ **Troubleshooting**: Solutions to common issues
- ✅ **Best Practices**: Industry-standard testing patterns

The Membership Testing Infrastructure is designed to be **developer-friendly**, **extensible**, and **maintainable**, providing a solid foundation for ensuring the quality and reliability of the membership module.

---

**Usage Guide Version**: 1.0  
**Last Updated**: December 2025  
**Next Review**: As needed  
**Maintainer**: Development Team  
**Status**: Production Ready ✅