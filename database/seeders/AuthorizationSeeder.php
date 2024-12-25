<?php

namespace Database\Seeders;

use App\Auth\Permission;
use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

// depends on: UserSeeder creating admin@aspirecloud.io
class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        // role creation was moved into UserSeeder, since user creation assigns the User role
        $this->createPermissions();
        $this->assignRoles();
    }

    private function createPermissions(): void
    {
        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value);
        }

        $user_perms = [Permission::SearchResources, Permission::ReadResource];
        RoleModel::findByName(Role::User->value)->givePermissionTo(...$user_perms);
        RoleModel::findByName(Role::Guest->value)->givePermissionTo(...$user_perms);

        $repo_admin_perms = [Permission::CreateResource, Permission::DeleteResource, ...$user_perms];
        RoleModel::findByName(Role::RepoAdmin->value)->givePermissionTo(...$repo_admin_perms);

        // SuperAdmins typically bypass permission checks, but it's still useful to grant all perms explicitly
        RoleModel::findByName(Role::SuperAdmin->value)->givePermissionTo(...Permission::cases());
    }

    private function assignRoles(): void
    {
        $admin = User::where('email', 'admin@aspirecloud.io')->firstOrFail();
    }
}
