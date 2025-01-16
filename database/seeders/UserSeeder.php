<?php

namespace Database\Seeders;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdminUser();
    }

    private function createAdminUser(): void
    {
        if (!User::where('email', 'admin@aspirecloud.io')->exists()) {
            $password = getenv('ADMIN_PASSWORD') ?: uniqid('', true);

            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@aspirecloud.io',
                'password' => Hash::make($password),
            ]);
            $admin->syncRoles(Role::SuperAdmin);
        }
    }
}
