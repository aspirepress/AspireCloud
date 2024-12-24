<?php

namespace Database\Seeders;

use App\Auth\Permission;
use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

// depends on: UserSeeder
class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->createRoles();
        $this->createPermissions();
        $this->assignRoles();
    }

    private function createRoles(): void
    {
        foreach (Role::cases() as $role) {
            RoleModel::findOrCreate($role->value);
        }
    }

    private function createPermissions(): void
    {
        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value);
        }

        RoleModel::findByName(Role::SuperAdmin->value)
            ->givePermissionTo(...Permission::cases());

        $user_perms = [
            Permission::SearchAllResources,
            Permission::ReadAnyResource,
            Permission::SearchPlugins,
            Permission::ReadAnyPlugin,
            Permission::SearchThemes,
            Permission::ReadAnyTheme,
        ];

        RoleModel::findByName(Role::User->value)->givePermissionTo(...$user_perms);
        RoleModel::findByName(Role::Guest->value)->givePermissionTo(...$user_perms);
    }

    private function assignRoles(): void
    {
        $admin = User::where('email', 'admin@aspirecloud.io')->firstOrFail();
        $admin->assignRole(Role::SuperAdmin);
    }
}
