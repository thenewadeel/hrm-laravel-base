<?php

namespace App\Http\Controllers;

use App\Models\Accounting\FinancialYear;
use App\Services\FinancialYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialYearController extends Controller
{
    protected FinancialYearService $financialYearService;

    public function __construct(FinancialYearService $financialYearService)
    {
        $this->financialYearService = $financialYearService;
    }

    public function create(): View
    {
        return view('accounting.financial-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $validated['organization_id'] = auth()->user()->current_organization_id;

        $this->financialYearService->createFinancialYear($validated);

        return redirect()->route('accounting.financial-years.index')
            ->with('success', 'Financial year created successfully.');
    }

    public function edit(FinancialYear $financialYear): View
    {
        return view('accounting.financial-years.edit', compact('financialYear'));
    }

    public function update(Request $request, FinancialYear $financialYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string',
        ]);

        $financialYear->update($validated);

        return redirect()->route('accounting.financial-years.index')
            ->with('success', 'Financial year updated successfully.');
    }

    public function destroy(FinancialYear $financialYear): RedirectResponse
    {
        if ($financialYear->status === 'active') {
            return redirect()->route('accounting.financial-years.index')
                ->with('error', 'Cannot delete active financial year.');
        }

        $financialYear->delete();

        return redirect()->route('accounting.financial-years.index')
            ->with('success', 'Financial year deleted successfully.');
    }

    public function activate(FinancialYear $financialYear): RedirectResponse
    {
        $this->financialYearService->activateFinancialYear($financialYear);

        return redirect()->route('accounting.financial-years.index')
            ->with('success', 'Financial year activated successfully.');
    }

    public function lock(FinancialYear $financialYear): RedirectResponse
    {
        $financialYear->update([
            'is_locked' => true,
            'locked_by' => auth()->id(),
            'locked_at' => now(),
        ]);

        return redirect()->route('accounting.financial-years.index')
            ->with('success', 'Financial year locked successfully.');
    }

    public function unlock(FinancialYear $financialYear): RedirectResponse
    {
        $financialYear->update([
            'is_locked' => false,
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return redirect()->route('accounting.financial-years.index')
            ->with('success', 'Financial year unlocked successfully.');
    }
}
