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

        // Validation
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::exists('users', 'email'),
                function ($attribute, $value, $fail) use ($organization) {
                    $user = User::where('email', $value)->first();
                    if ($user && $organization->users()->where('user_id', $user->id)->exists()) {
                        $fail('This user is already a member of the organization');
                    }
                },
            ],
            'roles' => ['required', 'string', Rule::in(['admin', 'manager', 'member'])],
        ]);

        // Find user
        $user = User::where('email', $validated['email'])->firstOrFail();

        // Add to organization with proper JSON encoding
        $organization->users()->attach(
            $user->id,
            [
                'roles' => [$validated['roles']],
            ]
        );

        return response()->json([
            'message' => 'User invited successfully',
            'data' => [
                'user_id' => $user->id,
                'roles' => $validated['roles'],
            ],
        ], 201);
    }
}
