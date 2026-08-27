<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $query = OrganizationUnit::with('parent')->where('organization_id', $currentOrganizationId);

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $units = $query->get();

        $employeeCounts = Employee::where('organization_id', $currentOrganizationId)
            ->selectRaw('organization_unit_id, count(*) as total')
            ->groupBy('organization_unit_id')
            ->pluck('total', 'organization_unit_id');

        $topLevelUnits = $units->whereNull('parent_id');

        return view('organizations.units.index', compact('units', 'topLevelUnits', 'employeeCounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $parentUnits = OrganizationUnit::where('organization_id', $currentOrganizationId)
            ->orderBy('name')
            ->get();

        return view('organizations.units.create', compact('parentUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(['department', 'branch', 'division', 'head_office'])],
            'parent_id' => 'nullable|exists:organization_units,id',
        ]);

        OrganizationUnit::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'organization_id' => $currentOrganizationId,
        ]);

        return redirect()->route('organization.units.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrganizationUnit $department)
    {
        $this->scoped($department);

        $currentOrganizationId = auth()->user()->current_organization_id;

        $parentUnits = OrganizationUnit::where('organization_id', $currentOrganizationId)
            ->where('id', '!=', $department->id)
            ->orderBy('name')
            ->get();

        return view('organizations.units.edit', compact('department', 'parentUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrganizationUnit $department)
    {
        $this->scoped($department);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in(['department', 'branch', 'division', 'head_office'])],
            'parent_id' => 'nullable|exists:organization_units,id',
        ]);

        $department->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] !== $department->id ? ($validated['parent_id'] ?? null) : $department->parent_id,
        ]);

        return redirect()->route('organization.units.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrganizationUnit $department)
    {
        $this->scoped($department);

        $department->delete();

        return redirect()->route('organization.units.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Ensure the unit belongs to the current organization.
     */
    private function scoped(OrganizationUnit $department): void
    {
        if ($department->organization_id !== auth()->user()->current_organization_id) {
            abort(403);
        }
    }
}
