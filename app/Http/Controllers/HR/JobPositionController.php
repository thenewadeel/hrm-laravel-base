<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobPosition;
use App\Models\OrganizationUnit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JobPositionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $query = JobPosition::with(['organizationUnit'])
            ->where('organization_id', $currentOrganizationId);

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('code', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('organization_unit_id', $request->department);
        }

        // Filter by active status
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $positions = $query->paginate(20);
        $departments = OrganizationUnit::where('organization_id', $currentOrganizationId)->get();

        return view('hr.positions.index', compact('positions', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organizationUnits = OrganizationUnit::where(
            'organization_id',
            auth()->user()->current_organization_id
        )->get();

        return view('hr.positions.create', compact('organizationUnits'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $currentOrganizationId = auth()->user()->current_organization_id;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('job_positions', 'code'),
            ],
            'description' => 'nullable|string|max:1000',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['organization_id'] = $currentOrganizationId;

        JobPosition::create($validated);

        return redirect()->route('hr.positions.index')
            ->with('success', 'Job position created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(JobPosition $position)
    {
        $this->authorize('view', $position);

        $position->load(['organizationUnit', 'employees']);

        return view('hr.positions.show', compact('position'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JobPosition $position)
    {
        // $this->authorize('update', $position);

        $organizationUnits = OrganizationUnit::where(
            'organization_id',
            auth()->user()->current_organization_id
        )->get();

        return view('hr.positions.edit', compact('position', 'organizationUnits'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JobPosition $position)
    {
        $this->authorize('update', $position);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('job_positions', 'code')->ignore($position->id),
            ],
            'description' => 'nullable|string|max:1000',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'requirements' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $position->update($validated);

        return redirect()->route('hr.positions.index')
            ->with('success', 'Job position updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobPosition $position)
    {
        $this->authorize('delete', $position);

        $position->delete();

        return redirect()->route('hr.positions.index')
            ->with('success', 'Job position deleted successfully.');
    }
}
