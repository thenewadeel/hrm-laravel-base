<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'chart_of_account_id' => 'required|exists:chart_of_accounts,id',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:255',
            'bank_name' => 'required|string|max:255',
            'account_type' => 'required|string|in:checking,savings,investment',
            'currency' => 'required|string|size:3',
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|string|in:active,inactive',
        ]);

        $validated['organization_id'] = auth()->user()->current_organization_id;

        BankAccount::create($validated);

        return redirect()->route('accounting.bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }
}
