<?php

use App\Http\Controllers\SetupController;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Inventory\Store;
use App\Models\Scopes\OrganizationScope;
use Illuminate\Support\Facades\Route;

// Org Setup Routes
Route::prefix('setup')->name("setup.")->group(function () {
    // Step 1: Organization
    Route::get('/', function () {
        $user = auth()->user();

        if ($user->organizations()->count() > 0) {
            return redirect('/setup/stores');
        }

        return view('setup.organization');
    })->name('organization');


    // Step 2: Store
    Route::get('/stores', function () {
        $user = auth()->user();
        $organization = $user->organizations()->first();

        if (!$organization) {
            return redirect('/setup');
        }

        if (Store::forOrganization($organization->id)->count() > 0) {
            return redirect('/setup/accounts');
        }

        return view('setup.stores');
    })->name('stores');


    // Step 3: Chart of Accounts
    Route::get('/accounts', function () {
        $user = auth()->user();
        // $org_user = OrganizationUser::where('user_id', $user->id)->first(); //TODO : refactor
        $organization_id = $user->operating_organization_id; //$org_user->organization_id;
        // dd([
        //     // 'accounts',
        //     "organization_id" => $organization_id,
        //     // "org_user" => (OrganizationUser::where('user_id', $user->id)->first()),
        //     // "stores" => Store::get(),
        //     "org_stores" => Store::forOrganization($organization_id)->get(),
        // ]);

        if (!$organization_id) {
            return redirect('/setup');
        }

        if (Store::forOrganization($organization_id)->count() === 0) {
            return redirect('/setup/stores');
        }

        if (ChartOfAccount::withoutGlobalScope(OrganizationScope::class)->where('organization_id', $organization_id)->count() > 0) {
            return redirect('/dashboard');
        }

        return view('setup.accounts');
    })->name('accounts');

    Route::post('/organization', [SetupController::class, 'storeOrganization'])
        ->name('organization.store');
    Route::post('/stores', [SetupController::class, 'storeStore'])
        ->name('stores.store');
    Route::post('/accounts', [SetupController::class, 'storeAccounts'])
        ->name('accounts.store');
});
