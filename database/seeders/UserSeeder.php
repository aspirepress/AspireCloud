<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!User::where('email', 'admin@aspirecloud.io')->exists()) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@aspirecloud.io',
            ]);
        }
    }
}
