<?php

namespace App\Policies;

use App\Models\Accounting\Expense;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(AccountingPermissions::VIEW_EXPENSES);
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->hasPermission(AccountingPermissions::VIEW_EXPENSES) &&
            $user->organizations->contains($expense->organization_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(AccountingPermissions::CREATE_EXPENSES);
    }

    public function update(User $user, Expense $expense): bool
    {
        // Can only update draft/unapproved expenses
        return $user->hasPermission(AccountingPermissions::EDIT_EXPENSES) &&
            $user->organizations->contains($expense->organization_id) &&
            $expense->isDraft();
    }

    public function delete(User $user, Expense $expense): bool
    {
        // Can only delete draft/unapproved expenses
        return $user->hasPermission(AccountingPermissions::DELETE_EXPENSES) &&
            $user->organizations->contains($expense->organization_id) &&
            $expense->isDraft();
    }

    public function approve(User $user, Expense $expense): bool
    {
        return $user->hasPermission(AccountingPermissions::APPROVE_EXPENSES) &&
            $user->organizations->contains($expense->organization_id) &&
            $expense->isPendingApproval();
    }

    public function reject(User $user, Expense $expense): bool
    {
        return $user->hasPermission(AccountingPermissions::REJECT_EXPENSES) &&
            $user->organizations->contains($expense->organization_id) &&
            $expense->isPendingApproval();
    }
}
