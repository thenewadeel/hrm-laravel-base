<?php

use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\OrganizationInvitationController;
use App\Http\Controllers\Api\OrganizationUnitController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
});
