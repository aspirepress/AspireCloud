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
        return $user->hasPermissionTo(Permission::CreateResource);
    }

    public function delete(User $user, Plugin $plugin): bool
    {
        return $user->hasPermissionTo(Permission::DeleteResource);
    }

    // no update method -- resources are immutable
}
