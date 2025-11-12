<?php

namespace App\Policies;

use App\Models\Accounting\JournalEntry;
use App\Models\User;
use App\Permissions\AccountingPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class JournalEntryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(AccountingPermissions::VIEW_JOURNAL_ENTRIES);
    }

    public function view(User $user, JournalEntry $entry): bool
    {
        return $user->hasPermission(AccountingPermissions::VIEW_JOURNAL_ENTRIES) &&
            $user->organizations->contains($entry->organization_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(AccountingPermissions::CREATE_JOURNAL_ENTRIES);
    }

    public function update(User $user, JournalEntry $entry): bool
    {
        // Can only update draft/unapproved entries
        return $user->hasPermission(AccountingPermissions::EDIT_JOURNAL_ENTRIES) &&
            $user->organizations->contains($entry->organization_id) &&
            $entry->isDraft();
    }

    public function delete(User $user, JournalEntry $entry): bool
    {
        // Can only delete draft/unapproved entries
        return $user->hasPermission(AccountingPermissions::DELETE_JOURNAL_ENTRIES) &&
            $user->organizations->contains($entry->organization_id) &&
            $entry->isDraft();
    }

    public function approve(User $user, JournalEntry $entry): bool
    {
        return $user->hasPermission(AccountingPermissions::APPROVE_JOURNAL_ENTRIES) &&
            $user->organizations->contains($entry->organization_id) &&
            $entry->isPendingApproval();
    }

    public function reject(User $user, JournalEntry $entry): bool
    {
        return $user->hasPermission(AccountingPermissions::REJECT_JOURNAL_ENTRIES) &&
            $user->organizations->contains($entry->organization_id) &&
            $entry->isPendingApproval();
    }
}
