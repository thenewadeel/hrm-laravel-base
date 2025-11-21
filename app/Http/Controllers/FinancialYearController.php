<?php

namespace App\Http\Controllers;

use App\Models\Accounting\FinancialYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FinancialYearController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // This is handled by the Livewire component
        return redirect()->route('accounting.financial-years.index');
    }

    public function update(Request $request, FinancialYear $financialYear): RedirectResponse
    {
        // This is handled by the Livewire component
        return redirect()->route('accounting.financial-years.index');
    }

    public function destroy(FinancialYear $financialYear): RedirectResponse
    {
        // This is handled by the Livewire component
        return redirect()->route('accounting.financial-years.index');
    }

    public function activate(FinancialYear $financialYear): RedirectResponse
    {
        // This is handled by the Livewire component
        return redirect()->route('accounting.financial-years.index');
    }

    public function lock(FinancialYear $financialYear): RedirectResponse
    {
        // This is handled by the Livewire component
        return redirect()->route('accounting.financial-years.index');
    }

    public function unlock(FinancialYear $financialYear): RedirectResponse
    {
        // This is handled by the Livewire component
        return redirect()->route('accounting.financial-years.index');
    }
}
