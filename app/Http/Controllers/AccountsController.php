<?php

namespace App\Http\Controllers;

use App\Models\Accounting\ChartOfAccount;
use Illuminate\Http\Request;

class AccountsController extends Controller
{
    public function index()
    {
        $accounts = ChartOfAccount::with('ledgerEntries')->get();
        return view('accounts.index', compact('accounts'));
    }
}
