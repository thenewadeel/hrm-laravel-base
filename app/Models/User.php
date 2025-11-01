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
        'operating_organization_id',
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
    // You might also add 'operating_organization_id' to $appends for API serialization
    // protected $appends = ['operating_organization_id'];

    public function getOperatingOrganizationIdAttribute(): ?int
    {
        // 1. Check for the explicitly set current organization ID
        if ($this->current_organization_id) {
            return (int) $this->current_organization_id;
        }

        // 2. Fallback to the first organization if one exists
        // (Ensure 'organizations' relationship is loaded before calling this if used outside the query)
        if ($this->organizations->isNotEmpty()) {
            return (int) $this->organizations->first()->id;
        }

        // 3. Return null if no organization ID can be determined
        return null;
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
        $organization = $organization ?: $this->currentOrganization ?: $this->organizations()->first();
        if (!$organization) {
            return false;
        }

        $membership = $this->organizations()->where('organizations.id', $organization->id)->first();

        if (!$membership) {
            return false;
        }

        // Fix: Ensure roles is always treated as array
        $roles = $membership->pivot->roles ?? [];

        // Handle case where roles might be stored as JSON string
        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?? [];
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $permissions = $this->getAllPermissions();
        // dd(['cp', $permission, $permissions]);

        return in_array($permission, $permissions);
    }

    // Also fix the addRole method around line 173
    public function addRole($role, $organization = null)
    {

        return $this->assignRole($role, $organization);
    }

    public function givePermissionTo(string|array $permissions, $organization = null): self
    {
        // 1. Ensure $permissions is an array for merging
        $permissionsToAdd = (array) $permissions;

        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;

            // Use withPivot(['permissions']) if necessary to ensure the column is loaded
            $membership = $this->organizations()
                ->where('organization_id', $orgId)
                ->first();

            if ($membership) {
                // dd($membership);
                // 2. RETRIEVE CURRENT PERMISSIONS (Laravel casting ensures this is an array)
                // If the column is null in the DB, the cast will return null, so use the null coalescing operator.
                $currentPermissions = $membership->pivot->permissions ?? [];

                // 3. APPEND (Merge the current array with the new permissions)
                $newPermissions = array_unique(array_merge($currentPermissions, $permissionsToAdd));

                // 4. SAVE (Laravel will automatically JSON-encode the array back to the DB)
                $this->organizations()->updateExistingPivot($orgId, [
                    // The 'permissions' attribute is now an array
                    'permissions' => $newPermissions
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
        $organization = $organization ?: $this->currentOrganization;

        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();

            if ($membership) {
                $currentRoles = $membership->pivot->roles ?? [];

                // Ensure currentRoles is always an array
                if (is_string($currentRoles)) {
                    $currentRoles = json_decode($currentRoles, true) ?? [];
                }

                if (!is_array($currentRoles)) {
                    $currentRoles = [$currentRoles];
                }

                // Ensure $role is an array for array_merge
                $rolesToAdd = is_array($roles) ? $roles : [$roles];
                $newRoles = array_unique(array_merge($currentRoles, $rolesToAdd));

                $this->organizations()->updateExistingPivot($orgId, [
                    'roles' => json_encode($newRoles)
                ]);
            }
        }

        return $this;
    }

    protected function getPermissionsForRoles(array $roles): array
    {
        // dd($roles);
        $allPermissions = [];
        foreach ($roles as $role) {
            $rolePermissions = InventoryRoles::getPermissionsForRole($role);
            $allPermissions = array_merge($allPermissions, $rolePermissions);
        }
        return array_unique($allPermissions);
    }

    public function getAllPermissions(): array
    {
        $allPermissions = [];
        foreach ($this->organizations as $org) {
            $directPermissions = $org->pivot->permissions ?? [];
            $allPermissions = array_merge($allPermissions, $directPermissions);

            $userRoles = $org->pivot->roles ?? [];
            $rolePermissions = $this->getPermissionsForRoles($userRoles);
            $allPermissions = array_merge($allPermissions, $rolePermissions);
            // dd([$org->pivot->permissions, $allPermissions, $userRoles, $rolePermissions]);
        }
        return array_unique($allPermissions);
    }

    public function getAllRoles(): array
    {
        $allRoles = [];

        foreach ($this->organizations as $org) {
            $roles = $org->pivot->roles ?? [];

            // Handle case where roles is a JSON string
            if (is_string($roles)) {
                $decodedRoles = json_decode($roles, true);
                $roles = is_array($decodedRoles) ? $decodedRoles : [];
            }

            $allRoles = array_merge($allRoles, $roles);
        }

        return array_unique($allRoles);
    }
}
