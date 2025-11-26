<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    protected $table = 'organization_user';

    protected function casts(): array
    {
        return [
            'roles' => 'array',
            'permissions' => 'array',
            'custom_fields' => 'array',
            'position' => 'string',
            'organization_id' => 'integer',
            'organization_unit_id' => 'integer',
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        $roles = $this->getAttribute('roles') ?? [];

        // Ensure we always have an array
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
        $userRoles = $this->getAttribute('roles') ?? [];

        // Ensure we always have an array
        if (is_string($userRoles)) {
            $userRoles = json_decode($userRoles, true) ?? [];
        }

        return ! empty(array_intersect($roles, $userRoles));
    }
}
