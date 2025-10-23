<?php

use App\Http\Controllers\Api\Accounting\ChartOfAccountsController;
use App\Http\Controllers\Api\Accounting\JournalEntriesController;
use App\Http\Controllers\Api\Accounting\FinancialReportsController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationInvitationController;
use App\Http\Controllers\Api\OrganizationUnitController;
use App\Models\Accounting\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Inventory\StoreController;
use App\Http\Controllers\Api\Inventory\ItemController;
use App\Http\Controllers\Api\Inventory\TransactionController;
use App\Http\Middleware\CheckInventoryPermission;
use App\Permissions\InventoryPermissions;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/**
 * @OA\Info(title="Attendance System API", version="0.1")
 */

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

Route::middleware('auth:sanctum')->group(function () {
    /**
     * @OA\Tag(name="Organizations")
     */
    /**
     * @OA\Get(
     *     path="/api/organizations",
     *     tags={"Organizations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="List organizations")
     * )
     */
    // User organizations
    Route::get('/users/me/organizations', function (Request $request) {
        return response()->json([
            'data' => $request->user()->organizations
        ]);
    });

    // Organization CRUD
    Route::apiResource('organizations', OrganizationController::class);

    // Organization-specific routes
    Route::prefix('organizations/{organization}')->group(function () {
        // Members
        Route::get('members', [OrganizationController::class, 'members']);

        // Invitations
        Route::post('invitations', [OrganizationInvitationController::class, 'store']);

        // Units - using singular 'unit' for consistency
        Route::prefix('units')->group(function () {
            Route::get('/', [OrganizationUnitController::class, 'index']);
            Route::post('/', [OrganizationUnitController::class, 'store']);

            // Specific unit operations
            Route::prefix('{unit}')->group(function () {
                Route::get('/', [OrganizationUnitController::class, 'show']);
                Route::put('/', [OrganizationUnitController::class, 'update']);
                Route::delete('/', [OrganizationUnitController::class, 'destroy']);

                // Unit-specific features
                Route::get('hierarchy', [OrganizationUnitController::class, 'hierarchy']);
                Route::get('members', [OrganizationUnitController::class, 'members']);
                Route::put('assign', [OrganizationUnitController::class, 'assignUser']);
                Route::post('bulk-assign', [OrganizationUnitController::class, 'bulkAssign']);
            });
        });
    });

    // Chart of Accounts routes
    Route::apiResource('accounts', ChartOfAccountsController::class);

    // Journal Entries routes
    Route::apiResource('journal-entries', JournalEntriesController::class);
    Route::put('journal-entries/{journal_entry}/post', [JournalEntriesController::class, 'post']);
    Route::put('journal-entries/{journal_entry}/void', [JournalEntriesController::class, 'void']);

    // Financial Reports routes
    Route::prefix('reports')->group(function () {
        Route::get('trial-balance', [FinancialReportsController::class, 'trialBalance']);
        Route::get('balance-sheet', [FinancialReportsController::class, 'balanceSheet']);
        Route::get('income-statement', [FinancialReportsController::class, 'incomeStatement']);
    });
});


// Temporary test route
Route::get('/debug/test-connection', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now(),
        'data' => JournalEntry::count() // if you have this model
    ]);
});


Route::prefix('inventory')->middleware(['auth:sanctum'])->group(function () {

    // Stores - using policy authorization
    Route::apiResource('stores', StoreController::class);
    Route::post('stores/{store}/items', [StoreController::class, 'addItem'])
        ->name('stores.items.add');
    Route::put('stores/{store}/items/{item}', [StoreController::class, 'updateItemQuantity'])
        ->name('stores.items.update');
    Route::delete('stores/{store}/items/{item}', [StoreController::class, 'removeItem'])
        ->name('stores.items.remove');

    // Items - using policy authorization
    Route::apiResource('items', ItemController::class);
    Route::get('items/{item}/availability', [ItemController::class, 'availability'])
        ->name('items.availability');
    Route::get('items/low-stock', [ItemController::class, 'lowStock'])
        ->name('items.low-stock');
    Route::get('items/out-of-stock', [ItemController::class, 'outOfStock'])
        ->name('items.out-of-stock');

    // Transactions - using policy authorization
    Route::apiResource('transactions', TransactionController::class);

    // Transaction actions with explicit permission checks
    Route::put('transactions/{transaction}/finalize', [TransactionController::class, 'finalize'])
        ->middleware('can:finalize,transaction')
        ->name('transactions.finalize');

    Route::put('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])
        ->middleware('can:cancel,transaction')
        ->name('transactions.cancel');

    // Store inventory management
    Route::put('stores/{store}/inventory', [StoreController::class, 'updateInventory'])
        ->middleware('can:manageInventory,store')
        ->name('stores.inventory.update');

    // Reports
    Route::get('reports/stock-levels', [\App\Http\Controllers\Api\Inventory\ReportController::class, 'stockLevels'])
        ->middleware('can:' . InventoryPermissions::VIEW_INVENTORY_REPORTS)
        ->name('inventory.reports.stock-levels');

    Route::get('reports/movement', [\App\Http\Controllers\Api\Inventory\ReportController::class, 'movement'])
        ->middleware('can:' . InventoryPermissions::VIEW_INVENTORY_REPORTS)
        ->name('inventory.reports.movement');
});
