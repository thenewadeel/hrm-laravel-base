<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class OrganizationInvitationController extends Controller
{
    public function store(Organization $organization, Request $request)
    {
        // Authorization
        if (Gate::denies('inviteMembers', $organization)) {
            abort(403, 'You are not authorized to invite members');
        }
        // dd($request);
        // Validation
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email'),
                function ($attribute, $value, $fail) use ($organization) {
                    if ($organization->users()->where('email', $value)->exists()) {
                        $fail('This user is already a member');
                    }
                }
            ],
            'roles' => ['required', Rule::in(['admin', 'manager', 'member'])]
        ]);
        // dd($validated);
        // Find user
        $user = User::where('email', $validated['email'])->firstOrFail();

        // Add to organization
        // $organization->users()->attach($user->id, [
        //     'roles' => [$validated['role']] // Store as array
        // ]);
        // Add to organization with manually encoded JSON
        $organization->users()->attach($user->id, [
            'roles' => json_encode([$validated['roles']]) // Explicit JSON encoding
        ]);

        return response()->json(null, 201);
    }
}
