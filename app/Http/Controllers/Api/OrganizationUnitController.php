<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use App\Models\OrganizationUnit;
use Illuminate\Support\Facades\Gate;

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
        // $this->authorize('create', [OrganizationUnit::class, $organization]);
        Gate::authorize('create', [OrganizationUnit::class, $organization]);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:organization_units,id,organization_id,' . $organization->id,
            // 'custom_fields' => 'nullable|array'
        ]);

        $orgData = [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'organization_id' => $organization->id,
            // 'custom_fields' => $validated['custom_fields'] ?? null,
        ];
        // if ($validated['parent_id']) {
        //     dd($orgData);
        // }
        $unit = $organization->units()->create($orgData);
        return response()->json([
            'data' => $unit
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Organization $organization, OrganizationUnit $unit)
    {
        // $this->authorize('view', [$unit, $organization]);

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
        // Authorization
        if (Gate::denies('view', $organization)) {
            abort(403, 'You are not authorized to invite members');
        }

        return response()->json([
            'data' => $unit->load(['children'])
        ]);
    }


    public function assignUser(
        Request $request,
        Organization $organization,
        OrganizationUnit $unit
    ) {
        // Verify unit belongs to organization
        if ($unit->organization_id !== $organization->id) {
            abort(403, 'This unit does not belong to the specified organization');
        }

        // // Authorization
        if (Gate::denies('assign', $unit)) {
            abort(403, 'You are not authorized to assign users to this unit');
        }

        // dd('ok');
        $validated = $request->validate([
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) use ($organization) {
                    if (!$organization->users()->where('user_id', $value)->exists()) {
                        $fail('The user is not a member of this organization');
                    }
                }
            ],
            'position' => 'nullable|string|max:255'
        ]);

        // Update the user's unit assignment
        $organization->users()->updateExistingPivot($validated['user_id'], [
            'organization_unit_id' => $unit->id,
            'position' => $validated['position'] ?? null
        ]);

        return response()->json([
            'message' => 'User assigned to unit successfully',
            'data' => [
                'user_id' => $validated['user_id'],
                'unit_id' => $unit->id,
                'position' => $validated['position'] ?? null
            ]
        ]);
    }
    // OrganizationUnitController.php
    public function members(Organization $organization, OrganizationUnit $unit)
    {
        // Authorization - verify user can view this organization's members
        if (Gate::denies('view', $organization)) {
            abort(403, 'You are not authorized to view members');
        }
        // $this->authorize('viewMembers', $organization);

        // Get all descendant unit IDs including the current unit
        $unitIds = $unit->allDescendants()
            ->pluck('id')
            ->push($unit->id);

        // Get users belonging to any of these units
        $members = $organization->users()
            ->whereIn('organization_user.organization_unit_id', $unitIds)
            ->with(['organizationUnits' => function ($query) use ($organization) {
                $query->where('organization_units.organization_id', $organization->id); // Explicit table name
            }])
            ->get()
            ->map(function ($user) {
                $unit = $user->organizationUnits
                    ->firstWhere('id', $user->pivot->organization_unit_id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'unit_id' => $user->pivot->organization_unit_id,
                    'unit_name' => $unit ? $unit->name : null,
                    'roles' => $user->pivot->roles
                ];
            });
        $x = response()->json(['data' => $members]);
        return $x;
    }
}
