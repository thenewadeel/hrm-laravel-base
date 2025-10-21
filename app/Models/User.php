<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_user')
            ->withPivot('organization_unit_id', 'position', 'roles', 'permissions')
            ->withTimestamps();
    }

    // app/Models/User.php

    public function units()
    {
        // This relationship correctly maps to the organization_user pivot table
        // by specifying the foreign keys. This seems okay.
        return $this->belongsToMany(OrganizationUnit::class, 'organization_user', 'user_id', 'organization_unit_id')
            ->withPivot('organization_id', 'position', 'roles', 'permissions')
            ->withTimestamps();
    }

    public function organizationUnits()
    {
        return $this->units();
    }


    /**
     * Check if user has permission for a specific organization
     */
    public function hasPermission(string $permission, $organization = null): bool
    {
        // If organization is provided, check permission in that context
        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();

            if ($membership && $membership->pivot->permissions) {
                $permissions = json_decode($membership->pivot->permissions, true) ?? [];
                return in_array($permission, $permissions);
            }
        }

        // Check if user has permission in any organization
        foreach ($this->organizations as $org) {
            if ($org->pivot->permissions) {
                $permissions = json_decode($org->pivot->permissions, true) ?? [];
                if (in_array($permission, $permissions)) {
                    return true;
                }
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

    /**
     * Check if user has role in specific organization
     */
    public function hasRole(string|array $roles, $organization = null): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        if ($organization) {
            $orgId = $organization instanceof Organization ? $organization->id : $organization;
            $membership = $this->organizations()->where('organizations.id', $orgId)->first();

            if ($membership && $membership->pivot->roles) {
                $userRoles = json_decode($membership->pivot->roles, true) ?? [];
                return !empty(array_intersect($roles, $userRoles));
            }
        }

        // Check if user has role in any organization
        foreach ($this->organizations as $org) {
            if ($org->pivot->roles) {
                $userRoles = json_decode($org->pivot->roles, true) ?? [];
                if (!empty(array_intersect($roles, $userRoles))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Assign role to user for specific organization
     */
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
     * Get all permissions for user across all organizations
     */
    public function getAllPermissions(): array
    {
        $allPermissions = [];

        foreach ($this->organizations as $org) {
            if ($org->pivot->permissions) {
                $permissions = json_decode($org->pivot->permissions, true) ?? [];
                $allPermissions = array_merge($allPermissions, $permissions);
            }
        }

        return array_unique($allPermissions);
    }
}
