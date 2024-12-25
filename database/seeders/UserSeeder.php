<?php

namespace Database\Seeders;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role as RoleModel;

class UserSeeder extends Seeder
{
    public function run()
    {
        $this->createRoles();
        $this->createAdminUser();
    }

    private function createAdminUser(): void
    {
        if (!User::where('email', 'admin@aspirecloud.io')->exists()) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@aspirecloud.io',
                'password' => Hash::make('qweqweqwe'),
            ]);
            $admin->syncRoles(Role::SuperAdmin);
        }
    }

    private function createRoles(): void
    {
        foreach (Role::cases() as $role) {
            RoleModel::findOrCreate($role->value);
        }
    }
}
