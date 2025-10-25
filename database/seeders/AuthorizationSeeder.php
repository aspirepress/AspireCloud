<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Auth\Permission;
use App\Auth\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        $this->createRoles();
        $this->createPermissions();
        $this->assignPermissions();
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
    }

    private function assignPermissions(): void
    {
        // SuperAdmins typically bypass permission checks, but it's still useful to grant all perms explicitly
        RoleModel::findByName(Role::SuperAdmin->value)->givePermissionTo(...Permission::cases());

        RoleModel::findByName(Role::RepoAdmin->value)
            ->givePermissionTo(Permission::UseAdminSite)
            ->givePermissionTo(Permission::BulkImport);
    }
}
