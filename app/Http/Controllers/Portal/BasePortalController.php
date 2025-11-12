<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\OrganizationUser;
use Illuminate\Support\Facades\Auth;

class BasePortalController extends Controller
{
    protected $currentUser;
    protected $organizationUser;
    protected $currentOrganization;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->currentUser = Auth::user();
            $this->currentOrganization = $this->currentUser->currentOrganization;
            $this->organizationUser = $this->getOrganizationUser();

            return $next($request);
        });
    }

    /**
     * Get the OrganizationUser pivot record for current user
     */
    protected function getOrganizationUser()
    {
        if (!$this->currentOrganization) {
            return null;
        }

        return OrganizationUser::where('user_id', $this->currentUser->id)
            ->where('organization_id', $this->currentOrganization->id)
            ->first();
    }

    /**
     * Check if user has specific role in current organization
     */
    protected function hasRole(string $role): bool
    {
        return $this->organizationUser && $this->organizationUser->hasRole($role);
    }

    /**
     * Check if user has any of the given roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        return $this->organizationUser && $this->organizationUser->hasAnyRole($roles);
    }

    /**
     * Authorize portal access based on roles
     */
    protected function authorizePortalAccess(array $allowedRoles = []): bool
    {
        if (!$this->currentOrganization) {
            abort(403, 'No organization selected');
        }

        if (!$this->organizationUser) {
            abort(403, 'Not a member of this organization');
        }

        if (!empty($allowedRoles) && !$this->hasAnyRole($allowedRoles)) {
            abort(403, 'Insufficient permissions for this portal');
        }

        return true;
    }

    /**
     * Get user's position in organization
     */
    protected function getUserPosition(): string
    {
        return $this->organizationUser->position ?? 'Employee';
    }
}
