<?php

namespace App\Policies;

use App\Models\Inventory\Item;
use App\Models\User;
use App\Permissions\InventoryPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission(InventoryPermissions::VIEW_ITEMS);
    }

    public function view(User $user, Item $item): bool
    {
        return $user->hasPermission(InventoryPermissions::VIEW_ITEMS) &&
            $user->organizations->contains($item->organization_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission(InventoryPermissions::CREATE_ITEMS);
    }

    public function update(User $user, Item $item): bool
    {
        return $user->hasPermission(InventoryPermissions::EDIT_ITEMS) &&
            $user->organizations->contains($item->organization_id);
    }

    public function delete(User $user, Item $item): bool
    {
        return $user->hasPermission(InventoryPermissions::DELETE_ITEMS) &&
            $user->organizations->contains($item->organization_id);
    }

    public function restore(User $user, Item $item): bool
    {
        return $user->hasPermission(InventoryPermissions::EDIT_ITEMS) &&
            $user->organizations->contains($item->organization_id);
    }

    public function forceDelete(User $user, Item $item): bool
    {
        return $user->hasPermission(InventoryPermissions::DELETE_ITEMS) &&
            $user->organizations->contains($item->organization_id);
    }
}
