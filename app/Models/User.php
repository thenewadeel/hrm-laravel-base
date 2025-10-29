<?php

namespace App\Models;

use App\Roles\InventoryRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_organization_id', // <-- ADDED for fixed tenancy
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
        'organization', // <-- ADDED for fixed tenancy
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- RELATIONSHIPS (CRITICAL FIXES HERE) ---

    public function organizations()
    {
        // CRITICAL FIX: Link to the custom pivot model
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->using(OrganizationUser::class)
            ->withPivot(['organization_unit_id', 'position', 'roles', 'permissions'])
            ->withTimestamps();
    }

    public function units()
    {
        // CRITICAL FIX: Link to the custom pivot model
        return $this->belongsToMany(OrganizationUnit::class, 'organization_user', 'user_id', 'organization_unit_id')
            ->using(OrganizationUser::class)
            ->withPivot(['organization_id', 'position', 'roles', 'permissions'])
            ->withTimestamps();
    }

    public function organizationUnits()
    {
        return $this->units();
    }

    // Fixed Tenancy Relationships
    public function currentOrganization()
    {
        return $this->belongsTo(Organization::class, 'current_organization_id');
    }

    public function getOrganizationAttribute()
    {
        return $this->currentOrganization;
    }

    // --- PERMISSION/ROLE METHODS (json_decode REMOVED) ---

    // Around line 101 and 116 - fix the hasPermission methods
    public function hasPermission($permission, $organization = null): bool
    {
        // If organization is provided, check permission in that context
        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();

            if ($membership) {
                // Check direct permissions
                if ($membership->pivot->permissions) {
                    $directPermissions = json_decode($membership->pivot->permissions, true) ?? [];
                    if (in_array($permission, $directPermissions)) {
                        return true;
                    }
                }

                // Check role-based permissions
                if ($membership->pivot->roles) {
                    $userRoles = json_decode($membership->pivot->roles, true) ?? [];
                    $rolePermissions = $this->getPermissionsForRoles($userRoles);
                    if (in_array($permission, $rolePermissions)) {
                        return true;
                    }
                }
            }
        }

        // Check if user has permission in any organization
        foreach ($this->organizations as $org) {
            $hasPermission = false;

            // Check direct permissions
            if ($org->pivot->permissions) {
                $directPermissions = json_decode($org->pivot->permissions, true) ?? [];
                if (in_array($permission, $directPermissions)) {
                    $hasPermission = true;
                }
            }

            // Check role-based permissions
            if (!$hasPermission && $org->pivot->roles) {
                $userRoles = json_decode($org->pivot->roles, true) ?? [];
                $rolePermissions = $this->getPermissionsForRoles($userRoles);
                if (in_array($permission, $rolePermissions)) {
                    $hasPermission = true;
                }
            }

            if ($hasPermission) {
                return true;
            }
        }

        return false;
    }

    /**
     * Give permission to user for a specific organization
     */
    public function givePermissionTo(string|array $permissions, $organization = null): self
    {
        $permissions = is_array($permissions) ? $permissions : [$permissions];

        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();

            if ($membership) {
                $currentPermissions = json_decode($membership->pivot->permissions, true) ?? [];
                $newPermissions = array_unique(array_merge($currentPermissions, $permissions));

                $this->organizations()->updateExistingPivot($orgId, [
                    'permissions' => json_encode($newPermissions)
                ]);
            }
        }
        return $this;
    }

    public function hasRole(string|array $roles, $organization = null): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();
            if ($membership) {
                $userRoles = $membership->pivot->roles ?? [];
                return !empty(array_intersect($roles, $userRoles));
            }
        }
        foreach ($this->organizations as $org) {
            $userRoles = $org->pivot->roles ?? [];
            if (!empty(array_intersect($roles, $userRoles))) {
                return true;
            }
        }
        return false;
    }

    public function assignRole(string|array $roles, $organization = null): self
    {
        $roles = is_array($roles) ? $roles : [$roles];

        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();

            if ($membership) {
                $currentRoles = json_decode($membership->pivot->roles, true) ?? [];
                $newRoles = array_unique(array_merge($currentRoles, $roles));

                $this->organizations()->updateExistingPivot($orgId, [
                    'roles' => json_encode($newRoles)
                ]);
            }
        }

        return $this;
    }

    /**
     * Get permissions for given roles
     */
    protected function getPermissionsForRoles(array $roles): array
    {
        $allPermissions = [];

        foreach ($roles as $role) {
            $rolePermissions = InventoryRoles::getPermissionsForRole($role);
            $allPermissions = array_merge($allPermissions, $rolePermissions);
        }

        return array_unique($allPermissions);
    }

    /**
     * Get all permissions for user across all organizations
     * Includes both direct permissions and role-based permissions
     */
    public function getAllPermissions(): array
    {
        $allPermissions = [];

        foreach ($this->organizations as $org) {
            // Get direct permissions
            if ($org->pivot->permissions) {
                $directPermissions = json_decode($org->pivot->permissions, true) ?? [];
                $allPermissions = array_merge($allPermissions, $directPermissions);
            }

            // Get role-based permissions
            if ($org->pivot->roles) {
                $userRoles = json_decode($org->pivot->roles, true) ?? [];
                $rolePermissions = $this->getPermissionsForRoles($userRoles);
                $allPermissions = array_merge($allPermissions, $rolePermissions);
            }
        }

        return array_unique($allPermissions);
    }

    /**
     * Get all roles for user across all organizations
     */
    public function getAllRoles(): array
    {
        $allRoles = [];

        foreach ($this->organizations as $org) {
            if ($org->pivot->roles) {
                $roles = json_decode($org->pivot->roles, true) ?? [];
                $allRoles = array_merge($allRoles, $roles);
            }
        }

        return array_unique($allRoles);
    }
}
