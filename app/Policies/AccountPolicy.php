<?php

namespace App\Policies;

use App\Models\Accounting\Account;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(AccountingPermissions::VIEW_CHART_OF_ACCOUNTS);
    }

    public function view(User $user, Account $account): bool
    {
        return $user->hasPermission(AccountingPermissions::VIEW_CHART_OF_ACCOUNTS) &&
            $user->organizations->contains($account->organization_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(AccountingPermissions::CREATE_CHART_OF_ACCOUNTS);
    }

    public function update(User $user, Account $account): bool
    {
        return $user->hasPermission(AccountingPermissions::EDIT_CHART_OF_ACCOUNTS) &&
            $user->organizations->contains($account->organization_id);
    }

    public function delete(User $user, Account $account): bool
    {
        return $user->hasPermission(AccountingPermissions::DELETE_CHART_OF_ACCOUNTS) &&
            $user->organizations->contains($account->organization_id);
    }
}
