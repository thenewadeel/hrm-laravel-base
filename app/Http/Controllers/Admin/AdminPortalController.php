<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\User;
use App\Roles\InventoryRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPortalController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in route definition
    }

    /**
     * Show admin dashboard with user-organization issues
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Only allow admins or users with appropriate permissions
        if (! $this->canAccessAdminPortal($user)) {
            abort(403, 'Unauthorized access to admin portal');
        }

        // Get users with organization attachment issues
        $usersWithIssues = $this->getUsersWithOrganizationIssues();

        // Get organizations for management
        $organizations = Organization::with(['users', 'units'])->get();

        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'users_with_orgs' => User::whereHas('organizations')->count(),
            'users_without_orgs' => User::whereDoesntHave('organizations')->count(),
            'total_orgs' => Organization::count(),
            'orphaned_org_users' => OrganizationUser::whereDoesntHave('user')->count(),
        ];

        return view('admin.dashboard', compact('usersWithIssues', 'organizations', 'stats'));
    }

    /**
     * Show form to attach user to organization
     */
    public function showAttachUserForm()
    {
        if (! $this->canAccessAdminPortal(auth()->user())) {
            abort(403);
        }

        $users = User::whereDoesntHave('organizations')
            ->orWhere('current_organization_id', null)
            ->orderBy('name')
            ->get();

        $organizations = Organization::with('units')->get();

        return view('admin.attach-user', compact('users', 'organizations'));
    }

    /**
     * Attach user to organization
     */
    public function attachUserToOrganization(Request $request)
    {
        if (! $this->canAccessAdminPortal(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'position' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => ['string', Rule::in([
                InventoryRoles::SUPER_ADMIN,
                InventoryRoles::INVENTORY_ADMIN,
                InventoryRoles::STORE_MANAGER,
                InventoryRoles::INVENTORY_CLERK,
                InventoryRoles::AUDITOR,
            ])],
            'set_as_current' => 'boolean',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $organization = Organization::findOrFail($validated['organization_id']);

        DB::transaction(function () use ($validated, $user, $organization) {
            // Check if user is already attached to this organization
            if ($user->organizations()->where('organizations.id', $organization->id)->exists()) {
                throw new \Exception('User is already attached to this organization');
            }

            // Get or create default unit if not specified
            $unitId = $validated['organization_unit_id'] ?? null;
            if (! $unitId) {
                $unit = $organization->units()->where('type', 'head_office')->first();
                if (! $unit) {
                    $unit = $organization->units()->create([
                        'name' => 'General',
                        'type' => 'department',
                        'parent_id' => null,
                    ]);
                }
                $unitId = $unit->id;
            }

            // Attach user to organization
            $user->organizations()->attach($organization->id, [
                'organization_unit_id' => $unitId,
                'position' => $validated['position'] ?? 'Staff',
                'roles' => json_encode($validated['roles'] ?? [InventoryRoles::INVENTORY_USER]),
            ]);

            // Set as current organization if requested
            if ($validated['set_as_current'] ?? false) {
                $user->current_organization_id = $organization->id;
                $user->save();
            }
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User successfully attached to organization');
    }

    /**
     * Detach user from organization
     */
    public function detachUserFromOrganization(Request $request)
    {
        if (! $this->canAccessAdminPortal(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $organization = Organization::findOrFail($validated['organization_id']);

        DB::transaction(function () use ($user, $organization) {
            // Detach user from organization
            $user->organizations()->detach($organization->id);

            // Clear current organization if it was the detached one
            if ($user->current_organization_id == $organization->id) {
                // Set to first available organization or null
                $firstOrg = $user->organizations()->first();
                $user->current_organization_id = $firstOrg?->id;
                $user->save();
            }
        });

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User successfully detached from organization');
    }

    /**
     * Fix user's current organization
     */
    public function fixUserCurrentOrganization(Request $request)
    {
        if (! $this->canAccessAdminPortal(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'organization_id' => 'required|exists:organizations,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $organization = Organization::findOrFail($validated['organization_id']);

        // Verify user is attached to the organization
        if (! $user->organizations()->where('organizations.id', $organization->id)->exists()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'User is not attached to the specified organization');
        }

        $user->current_organization_id = $organization->id;
        $user->save();

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'User current organization fixed successfully');
    }

    /**
     * Get users with organization attachment issues
     */
    private function getUsersWithOrganizationIssues()
    {
        return User::select('users.*')
            ->leftJoin('organization_user', 'users.id', '=', 'organization_user.user_id')
            ->where(function ($query) {
                $query->whereNull('organization_user.user_id')
                    ->orWhere('users.current_organization_id', null)
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNotNull('organization_user.user_id')
                            ->whereNotIn('users.current_organization_id', function ($innerQuery) {
                                $innerQuery->select('organization_id')
                                    ->from('organization_user')
                                    ->whereColumn('organization_user.user_id', 'users.id');
                            });
                    });
            })
            ->with('organizations')
            ->orderBy('users.name')
            ->get();
    }

    /**
     * Check if user can access admin portal
     */
    private function canAccessAdminPortal(User $user): bool
    {
        // Super admin check (you might want to add an is_admin field to users table)
        if ($user->email === config('app.admin_email', 'admin@example.com')) {
            return true;
        }

        // Check if user has admin role in any organization
        foreach ($user->organizations as $org) {
            $roles = $org->pivot->roles ?? [];
            if (is_string($roles)) {
                $roles = json_decode($roles, true) ?? [];
            }

            if (in_array(InventoryRoles::INVENTORY_ADMIN, $roles) ||
                in_array('system_admin', $roles)) {
                return true;
            }
        }

        return false;
    }
}
