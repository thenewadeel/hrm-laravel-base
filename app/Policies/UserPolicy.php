<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function view(User $user, User $model)
    {
        // Users can only view employees in their organization
        return $user->current_organization_id === $model->current_organization_id;
    }

    public function update(User $user, User $model)
    {
        // Only HR and managers can update employee details
        return $user->current_organization_id === $model->current_organization_id &&
            $user->currentOrganizationUser->hasAnyRole(['hr', 'manager']);
    }

    public function create(User $user)
    {
        // Only HR can create new employees
        return $user->currentOrganizationUser->hasRole('hr');
    }
}
