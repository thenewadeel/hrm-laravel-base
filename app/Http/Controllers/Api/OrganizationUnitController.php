<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\OrganizationUnit;

class OrganizationUnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // app/Http/Controllers/Api/OrganizationUnitController.php
    public function store(Request $request, Organization $organization)
    {
        $this->authorize('create', [OrganizationUnit::class, $organization]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_units,id,organization_id,' . $organization->id,
            'custom_fields' => 'nullable|array'
        ]);

        // Calculate depth
        // $depth = 0;
        // if ($validated['parent_id']) {
        //     $parent = OrganizationUnit::find($validated['parent_id']);
        //     $depth = $parent->depth + 1;
        // }

        // $unit = $organization->units()->create($validated);
        $unit = $organization->organizationUnits()->create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'],
            'depth' => $depth,
            'custom_fields' => $validated['custom_fields'] ?? null,
        ]);

        return response()->json([
            'data' => $unit
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization, OrganizationUnit $unit)
    {
        $this->authorize('view', [$unit, $organization]);

        return response()->json([
            'data' => $unit->load(['parent', 'children'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function hierarchy(Organization $organization, OrganizationUnit $unit)
    {
        $this->authorize('view', [$unit, $organization]);

        return response()->json([
            'data' => $unit->load(['allDescendants'])
        ]);
    }


    public function assignUser(Request $request, Organization $organization, OrganizationUnit $unit)
    {
        $this->authorize('assignUser', [$unit, $organization]);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'position' => 'nullable|string|max:255'
        ]);

        $organization->users()->updateExistingPivot($validated['user_id'], [
            'organization_unit_id' => $unit->id,
            'position' => $validated['position'] ?? null
        ]);

        return response()->json(null, 200);
    }
    // OrganizationUnitController.php
    public function members(Organization $organization, OrganizationUnit $unit)
    {
        $this->authorize('view', $organization);

        // Get all descendant unit IDs
        $unitIds = $unit->descendants()->pluck('id')->push($unit->id);

        $users = $organization->users()
            ->whereIn('organization_user.organization_unit_id', $unitIds)
            ->with('organizationUnits')
            ->get();

        return response()->json(['data' => $users]);
    }
}
