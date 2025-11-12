<?php

namespace App\Policies;

use App\Models\Inventory\Transaction;
use App\Models\User;
use App\Permissions\InventoryPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(InventoryPermissions::VIEW_TRANSACTIONS);
    }

    public function view(User $user, Transaction $transaction): bool
    {
        return $user->hasPermission(InventoryPermissions::VIEW_TRANSACTIONS) &&
            $user->organizations->contains($transaction->store->organization->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(InventoryPermissions::CREATE_TRANSACTIONS);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        // Can only update draft transactions
        return $user->hasPermission(InventoryPermissions::EDIT_TRANSACTIONS, $transaction->store->organization) &&
            $user->organizations->contains($transaction->store->organization->id) &&
            $transaction->isDraft(); // ✅ Add this check
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        // Can only delete draft transactions
        return $user->hasPermission(InventoryPermissions::DELETE_TRANSACTIONS) &&
            $user->organizations->contains($transaction->store->organization->id) &&
            $transaction->isDraft();
    }

    public function finalize(User $user, Transaction $transaction): bool
    {
        return $user->hasPermission(InventoryPermissions::FINALIZE_TRANSACTIONS) &&
            $user->organizations->contains($transaction->store->organization->id) &&
            $transaction->isDraft();
    }

    public function cancel(User $user, Transaction $transaction): bool
    {
        return $user->hasPermission(InventoryPermissions::CANCEL_TRANSACTIONS) &&
            $user->organizations->contains($transaction->store->organization->id) &&
            !$transaction->isCancelled();
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->hasPermission(InventoryPermissions::EDIT_TRANSACTIONS) &&
            $user->organizations->contains($transaction->store->organization->id);
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->hasPermission(InventoryPermissions::DELETE_TRANSACTIONS) &&
            $user->organizations->contains($transaction->store->organization->id);
    }
}
