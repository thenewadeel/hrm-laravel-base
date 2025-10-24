<?php
// app/Http/Controllers/SetupController.php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetupController extends Controller
{
    public function storeOrganization(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|min:3|max:255|unique:organizations,name',
        ]);

        DB::transaction(function () use ($validated) {
            // Create organization
            $organization = Organization::create([
                'name' => $validated['name'],
                'description' => 'Organization created during setup',
                'is_active' => true,
            ]);

            // Create root organization unit - be explicit about all fields
            $rootUnit = OrganizationUnit::create([
                'name' => 'Head Office',
                'type' => 'head_office',
                'organization_id' => $organization->id, // Explicitly set this
                'parent_id' => null,
                'custom_fields' => null, // Explicitly set to avoid factory defaults
            ]);

            // Attach user to organization with admin role
            auth()->user()->organizations()->attach($organization->id, [
                'roles' => json_encode(['admin']),
                'organization_unit_id' => $rootUnit->id,
                'position' => 'Administrator'
            ]);
            // dd([
            //     'organization' => json_encode($organization),
            //     'rootUnit' => json_encode($rootUnit),
            //     'user' => auth()->user(),
            //     'userOrganization' => auth()->user()->organizations()->first()
            // ]);
        });
        return redirect('/dashboard');
    }
}
