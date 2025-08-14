<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Organization::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:organizations',
            'description' => 'nullable|string'
        ]);

        $organization = Organization::create($validated);

        return response()->json([
            'data' => $organization
        ], 201);
    }

    public function show(Organization $organization)
    {
        return response()->json([
            'data' => $organization
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:organizations,name,' . $organization->id,
            'description' => 'nullable|string'
        ]);

        $organization->update($validated);

        return response()->json([
            'data' => $organization
        ]);
    }

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return response()->json(null, 204);
    }

    // In OrganizationController
    public function members(Organization $organization)
    {
        // $this->authorize('viewMembers', $organization);

        // Jetstream-style authorization
        if (Gate::denies('viewMembers', $organization)) {
            abort(403);
        }

        $members = $organization->users()
            ->withPivot('roles', 'organization_unit_id')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->pivot->roles,
                    'unit_id' => $user->pivot->organization_unit_id
                ];
            });

        return response()->json([
            'data' => $members
        ]);
    }
}
