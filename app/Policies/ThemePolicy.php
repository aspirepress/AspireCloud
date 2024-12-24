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
        return $user->hasAnyPermission(Permission::CreateAnyResource, Permission::CreateTheme);
    }

    public function update(User $user, Theme $theme): bool
    {
        return $user->hasAnyPermission(Permission::UpdateAnyResource, Permission::UpdateAnyTheme);
    }

    public function delete(User $user, Theme $theme): bool
    {
        return $user->hasAnyPermission(Permission::DeleteAnyResource, Permission::DeleteAnyTheme);
    }
}
