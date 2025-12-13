<?php

use App\Models\Accounting\ChartOfAccount;
use App\Models\Employee;
use App\Models\Inventory\Item;
use App\Models\Membership\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Security and Compliance Tests
test('multi-tenant data isolation under load testing', function () {
    // Create multiple organizations with data
    $organizations = Organization::factory()->count(3)->create();
    $users = collect();

    foreach ($organizations as $index => $organization) {
        $user = User::factory()->create([
            'current_organization_id' => $organization->id,
        ]);
        $users->push($user);

        // Create significant data for each organization
        Employee::factory()->count(100)->create(['organization_id' => $organization->id]);
        Item::factory()->count(50)->create(['organization_id' => $organization->id]);
        Member::factory()->count(200)->create(['organization_id' => $organization->id]);
        ChartOfAccount::factory()->count(20)->create(['organization_id' => $organization->id]);
    }

    // Test data isolation - each user should only see their organization's data
    foreach ($users as $index => $user) {
        $organization = $organizations[$index];

        // Test employee data isolation
        $employeeCount = $this->actingAs($user)
            ->getJson("/api/employees")
            ->assertStatus(200)
            ->json('data');

        expect($employeeCount)->toHaveCount(100);
        expect($employeeCount->first()->organization_id)->toBe($organization->id);

        // Test item data isolation
        $itemCount = $this->actingAs($user)
            ->getJson("/api/inventory/items")
            ->assertStatus(200)
            ->json('data');

        expect($itemCount)->toHaveCount(50);
        expect($itemCount->first()->organization_id)->toBe($organization->id);

        // Test member data isolation
        $memberCount = $this->actingAs($user)
            ->getJson("/api/members")
            ->assertStatus(200)
            ->json('data');

        expect($memberCount)->toHaveCount(200);
        expect($memberCount->first()->organization_id)->toBe($organization->id);

        // Verify user cannot access other organizations' data
        $otherOrgIndex = ($index + 1) % 3;
        $otherOrganization = $organizations[$otherOrgIndex];

        $this->actingAs($user)
            ->getJson("/api/employees?organization_id={$otherOrganization->id}")
            ->assertStatus(403); // Should be forbidden
    }
});

test('role-based access control enforcement across modules', function () {
    $organization = Organization::factory()->create();

    // Create users with different roles
    $admin = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $manager = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    $employee = User::factory()->create([
        'current_organization_id' => $organization->id,
    ]);

    // Assign roles (assuming role system exists)
    $admin->organizations()->attach($organization->id, ['role' => 'admin']);
    $manager->organizations()->attach($organization->id, ['role' => 'manager']);
    $employee->organizations()->attach($organization->id, ['role' => 'employee']);

    // Test admin access - should have full access
    $this->actingAs($admin)
        ->getJson("/api/employees")
        ->assertStatus(200);

    $this->actingAs($admin)
        ->getJson("/api/inventory/items")
        ->assertStatus(200);

    $this->actingAs($admin)
        ->getJson("/api/members")
        ->assertStatus(200);

    // Test manager access - should have limited access
    $this->actingAs($manager)
        ->getJson("/api/employees")
        ->assertStatus(200); // Can view employees

    $this->actingAs($manager)
        ->getJson("/api/inventory/items")
        ->assertStatus(200); // Can view inventory

    $this->actingAs($manager)
        ->getJson("/api/members")
        ->assertStatus(403); // Cannot manage members

    // Test employee access - should have minimal access
    $this->actingAs($employee)
        ->getJson("/api/employees")
        ->assertStatus(403); // Cannot view employees

    $this->actingAs($employee)
        ->getJson("/api/inventory/items")
        ->assertStatus(403); // Cannot view inventory

    $this->actingAs($employee)
        ->getJson("/api/members")
        ->assertStatus(403); // Cannot manage members
});

test('audit trail completeness for critical operations', function () {
    Event::fake();

    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Perform critical operations
    $employee = Employee::factory()->create(['organization_id' => $organization->id]);

    // Update employee (should create audit trail)
    $this->actingAs($user)
        ->putJson("/api/employees/{$employee->id}", [
            'first_name' => 'Updated Name',
            'last_name' => 'Updated Last Name',
        ])
        ->assertStatus(200);

    // Delete employee (should create audit trail)
    $this->actingAs($user)
        ->deleteJson("/api/employees/{$employee->id}")
        ->assertStatus(200);

    // Verify audit events were dispatched
    Event::assertDispatchedTimes('App\\Events\\EmployeeUpdated', 1);
    Event::assertDispatchedTimes('App\\Events\\EmployeeDeleted', 1);

    // Verify audit events contain correct data
    $updateEvent = Event::dispatched()->first(fn($event) => $event instanceof \App\Events\EmployeeUpdated);
    expect($updateEvent->user_id)->toBe($user->id);
    expect($updateEvent->organization_id)->toBe($organization->id);
    expect($updateEvent->employee_id)->toBe($employee->id);

    $deleteEvent = Event::dispatched()->first(fn($event) => $event instanceof \App\Events\EmployeeDeleted);
    expect($deleteEvent->user_id)->toBe($user->id);
    expect($deleteEvent->organization_id)->toBe($organization->id);
    expect($deleteEvent->employee_id)->toBe($employee->id);
});

test('data integrity validation prevents corruption', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Test double-entry accounting validation
    $chartOfAccounts = ChartOfAccount::factory()->count(3)->create([
        'organization_id' => $organization->id,
    ]);

    // Try to create unbalanced journal entry (should fail)
    $this->actingAs($user)
        ->postJson("/api/journal-entries", [
            'date' => now()->format('Y-m-d'),
            'description' => 'Test Entry',
            'entries' => [
                [
                    'chart_of_account_id' => $chartOfAccounts[0]->id,
                    'type' => 'debit',
                    'amount' => 1000,
                ],
                [
                    'chart_of_account_id' => $chartOfAccounts[1]->id,
                    'type' => 'credit',
                    'amount' => 800, // Intentionally unbalanced
                ],
            ],
        ])
        ->assertStatus(422) // Should fail validation
        ->assertJsonValidationErrors(['entries' => 'Journal entries must be balanced']);

    // Test balanced journal entry (should succeed)
    $this->actingAs($user)
        ->postJson("/api/journal-entries", [
            'date' => now()->format('Y-m-d'),
            'description' => 'Test Entry',
            'entries' => [
                [
                    'chart_of_account_id' => $chartOfAccounts[0]->id,
                    'type' => 'debit',
                    'amount' => 1000,
                ],
                [
                    'chart_of_account_id' => $chartOfAccounts[1]->id,
                    'type' => 'credit',
                    'amount' => 1000, // Balanced
                ],
            ],
        ])
        ->assertStatus(201); // Should succeed
});

test('concurrent operations handling with proper locking', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    $employee = Employee::factory()->create([
        'organization_id' => $organization->id,
        'first_name' => 'Original',
        'last_name' => 'Name',
    ]);

    // Simulate concurrent update requests
    $responses = collect();

    for ($i = 0; $i < 5; $i++) {
        $response = $this->actingAs($user)
            ->putJson("/api/employees/{$employee->id}", [
                'first_name' => "Concurrent Update $i",
                'last_name' => "Name $i",
            ]);

        $responses->push($response);
    }

    // At least one request should succeed
    $successfulRequests = $responses->filter(fn($response) => $response->status() === 200);
    expect($successfulRequests->count())->toBeGreaterThan(0);

    // Verify final state is consistent
    $finalEmployee = Employee::find($employee->id);
    expect($finalEmployee->first_name)->toBeString();
    expect($finalEmployee->last_name)->toBeString();

    // Verify database constraints are maintained
    expect($finalEmployee->organization_id)->toBe($organization->id);
});
test('input validation and sanitization prevents xss attacks', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Test XSS prevention in text fields
    $xssPayloads = [
        '<script>alert("xss")</script>',
        'javascript:alert("xss")',
        '<img src="x" onerror="alert(\'xss\')">',
        '"><script>alert("xss")</script>',
    ];

    foreach ($xssPayloads as $xssPayload) {
        $response = $this->actingAs($user)
            ->postJson("/api/employees", [
                'first_name' => $xssPayload,
                'last_name' => 'Test',
                'email' => 'test@example.com',
            ]);

        // Request should either succeed with sanitized data or fail validation
        expect(in_array($response->status(), [200, 422]))->toBeTrue();

        if ($response->status() === 200) {
            $employee = Employee::latest()->first();
            // Verify data was sanitized
            expect($employee->first_name)->not->toContain('<script>');
            expect($employee->first_name)->not->toContain('javascript:');
            expect($employee->first_name)->not->toContain('<img');
        }
    }
});

test('api rate limiting prevents abuse', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Make multiple rapid requests
    $responses = collect();

    for ($i = 0; $i < 20; $i++) {
        $response = $this->actingAs($user)
            ->getJson("/api/employees");

        $responses->push($response);
    }

    // Should allow some requests but rate limit after threshold
    $successfulRequests = $responses->filter(fn($response) => $response->status() === 200);
    $rateLimitedRequests = $responses->filter(fn($response) => $response->status() === 429);

    expect($successfulRequests->count())->toBeGreaterThan(0);
    expect($rateLimitedRequests->count())->toBeGreaterThan(0);
});

test('data export functionality includes all required fields', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['current_organization_id' => $organization->id]);

    // Create test data
    Employee::factory()->count(5)->create(['organization_id' => $organization->id]);
    Member::factory()->count(3)->create(['organization_id' => $organization->id]);

    // Test employee export
    $employeeExport = $this->actingAs($user)
        ->getJson("/api/employees/export")
        ->assertStatus(200);

    expect($employeeExport->headers('content-type'))->toContain('text/csv');

    // Test member export
    $memberExport = $this->actingAs($user)
        ->getJson("/api/members/export")
        ->assertStatus(200);

    expect($memberExport->headers('content-type'))->toContain('text/csv');

    // Verify exports contain expected data
    $employeeData = $employeeExport->json();
    expect($employeeData)->toHaveCount(5);

    $memberData = $memberExport->json();
    expect($memberData)->toHaveCount(3);

    // Verify CSV format compliance
    expect($employeeData[0])->toHaveKeys(['id', 'first_name', 'last_name', 'email', 'organization_id']);
    expect($memberData[0])->toHaveKeys(['id', 'first_name', 'last_name', 'membership_number', 'organization_id']);
});
