<?php

namespace App\Policies\Membership;

use App\Models\Membership\MemberSubscription;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MemberSubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('membership.view_subscriptions');
    }

    public function view(User $user, MemberSubscription $subscription): bool
    {
        return $user->hasPermissionTo('membership.view_subscriptions') 
            && $subscription->organization_id === $user->current_organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('membership.manage_subscriptions');
    }

    public function update(User $user, MemberSubscription $subscription): bool
    {
        return $user->hasPermissionTo('membership.manage_subscriptions') 
            && $subscription->organization_id === $user->current_organization_id;
    }

    public function delete(User $user, MemberSubscription $subscription): bool
    {
        return $user->hasPermissionTo('membership.manage_subscriptions') 
            && $subscription->organization_id === $user->current_organization_id;
    }

    public function restore(User $user, MemberSubscription $subscription): bool
    {
        return $user->hasPermissionTo('membership.manage_subscriptions') 
            && $subscription->organization_id === $user->current_organization_id;
    }

    public function forceDelete(User $user, MemberSubscription $subscription): bool
    {
        return $user->hasPermissionTo('membership.manage_subscriptions') 
            && $subscription->organization_id === $user->current_organization_id;
    }
}
