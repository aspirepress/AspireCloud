<?php

namespace App\Policies;

use App\Auth\Permission;
use App\Models\User;
use App\Models\WpOrg\Theme;

class ThemePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(User $user, Theme $theme): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CreateAnyResource);
    }

    public function delete(User $user, Theme $theme): bool
    {
        return $user->hasPermissionTo(Permission::DeleteAnyResource);
    }

    // no update method -- resources are immutable
}
