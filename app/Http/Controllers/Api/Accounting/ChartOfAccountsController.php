<?php

// app/Http/Controllers/Api/Accounting/ChartOfAccountsController.php

namespace App\Http\Controllers\Api\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChartOfAccountRequest;
use App\Http\Requests\UpdateChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;
use App\Models\Accounting\ChartOfAccount;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::where('organization_id', auth()->user()->current_organization_id)
            ->orderBy('code')
            ->paginate(15);

        return ChartOfAccountResource::collection($accounts);
    }

    public function store(StoreChartOfAccountRequest $request)
    {
        $account = ChartOfAccount::create($request->validated());

        return new ChartOfAccountResource($account);
    }

    public function show($id)
    {
        $account = ChartOfAccount::where('id', $id)
            ->where('organization_id', auth()->user()->current_organization_id)
            ->firstOrFail();

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
                'message' => 'Cannot delete account with transaction history',
            ], 422);
        }

        $account->delete();

        return response()->noContent();
    }
}
