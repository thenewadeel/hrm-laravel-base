<?php

use App\Http\Controllers\Api\OrganizationController;
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
    Route::apiResource('organizations', OrganizationController::class);
    Route::get('/users/me/organizations', function (Request $request) {
        return response()->json([
            'data' => $request->user()->organizations
        ]);
    });

    Route::get(
        '/organizations/{organization}/members',
        [OrganizationController::class, 'members']
    );
});
