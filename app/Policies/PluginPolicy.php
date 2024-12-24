<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\User;
use App\Models\WpOrg\Plugin;

class PluginPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(User $user, Plugin $plugin): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(Permission::CreateAnyResource, Permission::CreatePlugin);
    }

    public function update(User $user, Plugin $plugin): bool
    {
        return $user->hasAnyPermission(Permission::UpdateAnyResource, Permission::UpdateAnyPlugin);
    }

    public function delete(User $user, Plugin $plugin): bool
    {
        return $user->hasAnyPermission(Permission::DeleteAnyResource, Permission::DeleteAnyPlugin);
    }
}
