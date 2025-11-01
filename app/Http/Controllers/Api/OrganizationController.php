<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Permissions\OrganizationPermissions;
use App\Roles\OrganizationRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Organization::class);

        return response()->json([
            'data' => Organization::all()
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        // dd([
        //     $user->organizations,
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION),
        //     $user->hasRole(OrganizationRoles::SUPER_ADMIN)
        // ]);
        Gate::authorize('create', Organization::class);

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
        Gate::authorize('view', [Organization::class, $organization]);

        return response()->json([
            'data' => $organization
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $user = auth()->user();
        // dd([
        //     $user->organizations,
        //     $user->getAllRoles(),
        //     $user->getAllPermissions(),
        //     $user->hasPermission(OrganizationPermissions::CREATE_ORGANIZATION),
        //     $user->hasRole(OrganizationRoles::SUPER_ADMIN)
        // ]);
        Gate::authorize('update', [Organization::class, $organization]);

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
        Gate::authorize('delete', [Organization::class, $organization]);

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
