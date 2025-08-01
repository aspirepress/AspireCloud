<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AuthorizationSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(PluginSeeder::class);
        $this->call(OriginSeeder::class);
    }
}
