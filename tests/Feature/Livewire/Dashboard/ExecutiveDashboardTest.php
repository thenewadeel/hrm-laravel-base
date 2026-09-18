<?php

use App\Livewire\Dashboard\ExecutiveDashboard;
use App\Models\Inventory\Item;
use App\Models\Inventory\Store;
use App\Models\Membership\Member;
use App\Models\Membership\MemberSubscription;
use App\Models\Organization;
use App\Models\OrganizationUnit;
use App\Models\User;
use App\Models\UserDashboardPreference;
use App\Services\Dashboard\ExecutiveOverviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function executiveDashboardOrg(): array
{
    $organization = Organization::factory()->create();
    $unit = OrganizationUnit::factory()->create(['organization_id' => $organization->id]);
    $user = User::factory()->create(['current_organization_id' => $organization->id]);
    $user->organizations()->attach($organization->id, ['roles' => ['admin']]);

    return [$organization, $user, $unit];
}

test('renders with KPI and widget payload', function () {
    [$organization, $user] = executiveDashboardOrg();

    $this->actingAs($user);

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->assertStatus(200)
        ->assertSet('organizationId', $organization->id)
        ->assertSet('organizationName', $organization->name)
        ->assertCount('kpis', 7)
        ->assertSet('layout', ExecutiveOverviewService::DEFAULT_ORDER)
        ->assertHasNoErrors();
});

test('showcases organization-scoped data in widgets', function () {
    [$organization, $user, $unit] = executiveDashboardOrg();

    $store = Store::factory()->create(['organization_unit_id' => $unit->id, 'name' => 'Warehouse A']);
    $healthyItem = Item::factory()->create(['organization_id' => $organization->id, 'reorder_level' => 5]);
    $store->items()->attach($healthyItem->id, ['quantity' => 50, 'min_stock' => 5, 'max_stock' => 100]);

    $member = Member::factory()->create(['organization_id' => $organization->id]);
    MemberSubscription::factory()->active()->create([
        'organization_id' => $organization->id,
        'member_id' => $member->id,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(ExecutiveDashboard::class, ['organization' => $organization]);

    $component->assertSet('widgets.stock-allocation.labels', ['Warehouse A'])
        ->assertSet('widgets.stock-health.data', [
            ['label' => 'Healthy', 'value' => 1, 'color' => 'success'],
            ['label' => 'Low', 'value' => 0, 'color' => 'warning'],
            ['label' => 'Out', 'value' => 0, 'color' => 'error'],
        ])
        ->assertSet('widgets.subscription-health.data', [
            ['label' => 'Active', 'value' => 1, 'color' => 'success'],
            ['label' => 'Expiring', 'value' => 0, 'color' => 'warning'],
            ['label' => 'Expired', 'value' => 0, 'color' => 'error'],
        ])
        ->assertSet('activity.0.type', 'member');
});

test('does not leak data from another organization', function () {
    [$organization, $user] = executiveDashboardOrg();

    [$otherOrganization, , $otherUnit] = executiveDashboardOrg();
    $otherStore = Store::factory()->create(['organization_unit_id' => $otherUnit->id, 'name' => 'Other Warehouse']);

    $this->actingAs($user);

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->assertSet('widgets.stock-allocation.labels', [])
        ->assertDontSee('Other Warehouse');
});

test('reorder persists the layout per user', function () {
    [$organization, $user] = executiveDashboardOrg();

    $this->actingAs($user);

    $customOrder = array_reverse(ExecutiveOverviewService::DEFAULT_ORDER);

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->call('reorderWidgets', $customOrder)
        ->assertSet('layout', $customOrder)
        ->assertSet('saved', true);

    $this->assertDatabaseHas('user_dashboard_preferences', [
        'user_id' => $user->id,
        'organization_id' => $organization->id,
    ]);

    expect(UserDashboardPreference::where('user_id', $user->id)->first()->layout)
        ->toBe($customOrder);
});

test('reorder ignores unknown and duplicate keys', function () {
    [$organization, $user] = executiveDashboardOrg();

    $this->actingAs($user);

    $component = Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->call('reorderWidgets', ['bogus', 'not-real', 'activity-feed', 'activity-feed', 'revenue-overview'])
        ->assertSet('saved', true);

    $expected = ['activity-feed', 'revenue-overview'];
    foreach (ExecutiveOverviewService::DEFAULT_ORDER as $key) {
        if (! in_array($key, $expected, true)) {
            $expected[] = $key;
        }
    }

    $component->assertSet('layout', $expected);
});

test('toggle hides and reveals a widget and persists', function () {
    [$organization, $user] = executiveDashboardOrg();

    $this->actingAs($user);

    $without = collect(ExecutiveOverviewService::DEFAULT_ORDER)->reject(fn ($key) => $key === 'stock-health')->values()->all();

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->call('toggleWidget', 'stock-health')
        ->assertSet('layout', $without)
        ->call('toggleWidget', 'stock-health')
        ->assertSet('layout', [...$without, 'stock-health'])
        ->assertSet('saved', true);
});

test('toggle ignores unknown widget keys', function () {
    [$organization, $user] = executiveDashboardOrg();

    $this->actingAs($user);

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->call('toggleWidget', 'does-not-exist')
        ->assertSet('layout', ExecutiveOverviewService::DEFAULT_ORDER);
});

test('resurrects a saved preference for repeat visits', function () {
    [$organization, $user] = executiveDashboardOrg();

    $this->actingAs($user);

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->call('reorderWidgets', ['activity-feed', 'cash-position']);

    $expected = ['activity-feed', 'cash-position'];
    foreach (ExecutiveOverviewService::DEFAULT_ORDER as $key) {
        if (! in_array($key, $expected, true)) {
            $expected[] = $key;
        }
    }

    Livewire::test(ExecutiveDashboard::class, ['organization' => $organization])
        ->assertSet('layout', $expected);
});
