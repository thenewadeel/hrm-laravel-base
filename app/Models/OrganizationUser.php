<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    protected $table = 'organization_user';

    protected $casts = [
        'roles' => 'array',
        'permissions' => 'array',
        'custom_fields' => 'array',
        'position' => 'string',
        'organization_id' => 'integer',
        'organization_unit_id' => 'integer',
    ];
    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        $roles = $this->roles ?? [];

        // Handle case where roles might not be properly cast
        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?? [];
        }

        return in_array($role, $roles);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $userRoles = $this->roles ?? [];

        // Handle case where roles might not be properly cast
        if (is_string($userRoles)) {
            $userRoles = json_decode($userRoles, true) ?? [];
        }

        return !empty(array_intersect($roles, $userRoles));
    }

    /**
     * Get all roles as array (ensures proper casting)
     */
    public function getRolesAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            return json_decode($value, true) ?? [];
        }

        return [];
    }

    /**
     * Set roles attribute (ensures proper JSON encoding)
     */
    public function setRolesAttribute($value): void
    {
        if (is_string($value)) {
            // If it's already JSON, decode and re-encode to ensure consistency
            $decoded = json_decode($value, true);
            $this->attributes['roles'] = $decoded ? json_encode($decoded) : json_encode([]);
        } else {
            $this->attributes['roles'] = json_encode($value ?? []);
        }
    }
}
