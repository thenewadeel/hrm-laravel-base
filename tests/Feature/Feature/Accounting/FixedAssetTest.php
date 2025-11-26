<?php

use App\Models\Accounting\FixedAsset;
use App\Models\Accounting\FixedAssetCategory;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Organization;
use App\Models\User;
use App\Services\FixedAssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view fixed assets index', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['roles' => 'admin']);
    $user->current_organization_id = $organization->id;
    $user->save();

    $response = $this->actingAs($user)
        ->get(route('accounting.fixed-assets.index'));

    $response->assertStatus(200);
});

test('user can register a new fixed asset', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['roles' => 'admin']);
    $user->current_organization_id = $organization->id;
    $user->save();

    $category = FixedAssetCategory::factory()->create(['organization_id' => $organization->id]);
    $assetAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id, 'type' => 'asset']);
    $cashAccount = ChartOfAccount::factory()->create(['organization_id' => $organization->id, 'type' => 'asset', 'name' => 'Cash Account', 'code' => 'CASH']);

    $assetData = [
        'fixed_asset_category_id' => $category->id,
        'chart_of_account_id' => $assetAccount->id,
        'asset_tag' => 'AST-TEST-' . uniqid(),
        'name' => 'Test Computer',
        'description' => 'Test computer description',
        'serial_number' => 'SN123456',
        'location' => 'Main Office',
        'department' => 'IT',
        'assigned_to' => 'John Doe',
        'purchase_date' => '2024-01-01',
        'purchase_cost' => 1500.00,
        'salvage_value' => 150.00,
        'useful_life_years' => 5,
        'depreciation_method' => 'straight_line',
        'notes' => 'Test notes',
    ];

    $response = $this->actingAs($user)
        ->post(route('accounting.fixed-assets.store'), $assetData);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Asset registered successfully.',
    ]);

    $this->assertDatabaseHas('fixed_assets', [
        'organization_id' => $organization->id,
        'asset_tag' => 'AST-TEST-' . uniqid(),
        'name' => 'Test Computer',
    ]);
});

test('fixed asset calculates straight line depreciation correctly', function () {
    $asset = FixedAsset::factory()->create([
        'purchase_cost' => 10000,
        'salvage_value' => 1000,
        'useful_life_years' => 5,
        'depreciation_method' => 'straight_line',
    ]);

    $expectedDepreciation = ($asset->purchase_cost - $asset->salvage_value) / $asset->useful_life_years;

    expect($asset->calculateStraightLineDepreciation())->toBe($expectedDepreciation);
});

test('fixed asset calculates declining balance depreciation correctly', function () {
    $category = FixedAssetCategory::factory()->create(['default_depreciation_rate' => 20]);
    $asset = FixedAsset::factory()->create([
        'fixed_asset_category_id' => $category->id,
        'purchase_cost' => 10000,
        'current_book_value' => 8000,
        'salvage_value' => 1000,
        'depreciation_method' => 'declining_balance',
    ]);

    $expectedDepreciation = $asset->current_book_value * 0.20; // 20% of current book value

    expect($asset->calculateDecliningBalanceDepreciation())->toBe($expectedDepreciation);
});

test('fixed asset calculates sum of years depreciation correctly', function () {
    $asset = FixedAsset::factory()->create([
        'purchase_cost' => 10000,
        'salvage_value' => 1000,
        'useful_life_years' => 5,
        'depreciation_method' => 'sum_of_years',
        'last_depreciation_date' => now()->subYear(), // Second year
    ]);

    // Sum of years: 1+2+3+4+5 = 15
    // Second year: 4/15 * (10000-1000) = 2400
    $expectedDepreciation = 2400;

    expect($asset->calculateSumOfYearsDepreciation())->toBe($expectedDepreciation);
});

test('can post depreciation for asset', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['roles' => 'admin']);
    $user->current_organization_id = $organization->id;
    $user->save();

    $asset = FixedAsset::factory()->create([
        'organization_id' => $organization->id,
        'purchase_cost' => 10000,
        'accumulated_depreciation' => 2000,
        'current_book_value' => 8000,
        'last_depreciation_date' => now()->subYear(),
    ]);

    $fixedAssetService = app(FixedAssetService::class);
    $depreciation = $fixedAssetService->postDepreciation($asset);

    expect($depreciation)->toBeInstanceOf(\App\Models\Accounting\Depreciation::class);
    expect($depreciation->depreciation_amount)->toBeGreaterThan(0);

    $asset->refresh();
    expect($asset->accumulated_depreciation)->toBeGreaterThan(2000);
    expect($asset->current_book_value)->toBeLessThan(8000);
});

test('can dispose asset with gain', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['roles' => 'admin']);
    $user->current_organization_id = $organization->id;
    $user->save();

    $asset = FixedAsset::factory()->create([
        'organization_id' => $organization->id,
        'purchase_cost' => 10000,
        'accumulated_depreciation' => 3000,
        'current_book_value' => 7000,
    ]);

    $disposalData = [
        'disposal_date' => now()->format('Y-m-d'),
        'disposal_type' => 'sale',
        'proceeds' => 8000,
        'reason' => 'Asset sold',
    ];

    $fixedAssetService = app(FixedAssetService::class);
    $disposal = $fixedAssetService->disposeAsset($asset, $disposalData);

    expect($disposal->gain_loss)->toBe(1000); // 8000 - 7000 = 1000 gain

    $asset->refresh();
    expect($asset->status)->toBe('disposed');
});

test('can transfer asset between locations', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['roles' => 'admin']);
    $user->current_organization_id = $organization->id;
    $user->save();

    $asset = FixedAsset::factory()->create([
        'organization_id' => $organization->id,
        'location' => 'Main Office',
        'department' => 'IT',
        'assigned_to' => 'John Doe',
    ]);

    $transferData = [
        'transfer_date' => now()->format('Y-m-d'),
        'to_location' => 'Branch Office',
        'to_department' => 'Sales',
        'to_assigned_to' => 'Jane Smith',
        'reason' => 'Department reorganization',
    ];

    $fixedAssetService = app(FixedAssetService::class);
    $transfer = $fixedAssetService->transferAsset($asset, $transferData);

    expect($transfer)->toBeInstanceOf(\App\Models\Accounting\AssetTransfer::class);

    $asset->refresh();
    expect($asset->location)->toBe('Branch Office');
    expect($asset->department)->toBe('Sales');
    expect($asset->assigned_to)->toBe('Jane Smith');
});

test('can record asset maintenance', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create();
    $user->organizations()->attach($organization, ['roles' => 'admin']);
    $user->current_organization_id = $organization->id;
    $user->save();

    $asset = FixedAsset::factory()->create(['organization_id' => $organization->id]);

    $maintenanceData = [
        'maintenance_date' => now()->format('Y-m-d'),
        'maintenance_type' => 'repair',
        'description' => 'Fixed keyboard issue',
        'cost' => 150.00,
        'performed_by' => 'IT Support',
        'vendor' => 'Tech Repair Co.',
    ];

    $fixedAssetService = app(FixedAssetService::class);
    $maintenance = $fixedAssetService->recordMaintenance($asset, $maintenanceData);

    expect($maintenance)->toBeInstanceOf(\App\Models\Accounting\AssetMaintenance::class);
    expect($maintenance->cost)->toBe(150.00);
    expect($maintenance->maintenance_type)->toBe('repair');
});

test('asset knows when it is fully depreciated', function () {
    $asset = FixedAsset::factory()->create([
        'purchase_cost' => 10000,
        'salvage_value' => 1000,
        'current_book_value' => 1000, // Equal to salvage value
    ]);

    expect($asset->isFullyDepreciated())->toBeTrue();

    $asset->current_book_value = 999; // Below salvage value
    expect($asset->isFullyDepreciated())->toBeTrue();

    $asset->current_book_value = 1001; // Above salvage value
    expect($asset->isFullyDepreciated())->toBeFalse();
});

test('asset knows when it can be depreciated', function () {
    $activeAsset = FixedAsset::factory()->create([
        'status' => 'active',
        'purchase_cost' => 10000,
        'current_book_value' => 8000,
    ]);

    $inactiveAsset = FixedAsset::factory()->create([
        'status' => 'inactive',
        'purchase_cost' => 10000,
        'current_book_value' => 8000,
    ]);

    $fullyDepreciatedAsset = FixedAsset::factory()->create([
        'status' => 'active',
        'purchase_cost' => 10000,
        'salvage_value' => 1000,
        'current_book_value' => 1000,
    ]);

    expect($activeAsset->canBeDepreciated())->toBeTrue();
    expect($inactiveAsset->canBeDepreciated())->toBeFalse();
    expect($fullyDepreciatedAsset->canBeDepreciated())->toBeFalse();
});
