<?php
// app/Http/Controllers/Api/Accounting/ChartOfAccountsController.php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::orderBy('code')->get();

        return ChartOfAccountResource::collection($accounts);
    }

    public function store(StoreChartOfAccountRequest $request)
    {
        $account = ChartOfAccount::create($request->validated());

        return new ChartOfAccountResource($account);
    }

    public function show(ChartOfAccount $account)
    {
        return new ChartOfAccountResource($account);
    }

    public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $account)
    {
        $account->update($request->validated());

        return new ChartOfAccountResource($account);
    }

    public function destroy(ChartOfAccount $account)
    {
        // Prevent deletion if has ledger entries
        if ($account->ledgerEntries()->exists()) {
            return response()->json([
                'message' => 'Cannot delete account with transaction history'
            ], 422);
        }

        $account->delete();

        return response()->noContent();
    }
}
