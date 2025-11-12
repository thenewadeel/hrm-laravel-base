<?php

namespace App\Policies;

use App\Models\Inventory\Store;
use App\Models\User;
use App\Permissions\InventoryPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class StorePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(InventoryPermissions::VIEW_STORES);
    }

    public function view(User $user, Store $store): bool
    {
        return $user->hasPermission(InventoryPermissions::VIEW_STORES, $store->organization) &&
            $user->organizations->contains($store->organization->id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(InventoryPermissions::CREATE_STORES);
    }

    public function update(User $user, Store $store): bool
    {
        return $user->hasPermission(InventoryPermissions::EDIT_STORES, $store->organization) &&
            $user->organizations->contains($store->organization->id);
    }

    public function delete(User $user, Store $store): bool
    {
        return $user->hasPermission(InventoryPermissions::DELETE_STORES, $store->organization) &&
            $user->organizations->contains($store->organization->id);
    }

    public function manageInventory(User $user, Store $store): bool
    {
        return $user->hasPermission(InventoryPermissions::MANAGE_STORE_INVENTORY, $store->organization) &&
            $user->organizations->contains($store->organization->id);
    }

    public function restore(User $user, Store $store): bool
    {
        return $user->hasPermission(InventoryPermissions::EDIT_STORES) &&
            $user->organizations->contains($store->organization->id);
    }

    public function forceDelete(User $user, Store $store): bool
    {
        return $user->hasPermission(InventoryPermissions::DELETE_STORES) &&
            $user->organizations->contains($store->organization->id);
    }
}
