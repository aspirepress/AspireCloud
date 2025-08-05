<?php

namespace Database\Seeders;

use App\Models\Origin;
use App\Models\PackageType;
use Illuminate\Database\Seeder;

class OriginSeeder extends Seeder
{
    public function run(): void
    {
        $this->createOrigins();
        $this->createPackageTypes();
    }

    private function createOrigins(): void
    {
        // Do nothing if there are records.
        if (Origin::count() > 0) {
            return;
        }

        $initialOrigins = [
            'wp_org' => 'WordPress.org', // Legacy.
            'fair' => 'FAIR', // FAIR repositories.
        ];

        foreach ($initialOrigins as $slug => $name) {
            Origin::create(compact('slug', 'name'));
        }
    }

    private function createPackageTypes(): void
    {
        // Do nothing if there are records.
        if (PackageType::count() > 0) {
            return;
        }

        $initialTypes = [
            'wp_org' => [
                'plugin' => 'Plugin',
                'theme' => 'Theme',
                'closed_plugin' => 'Closed Plugin',
            ],
            'fair' => [
                'plugin' => 'Plugin',
                'theme' => 'Theme',
            ],
        ];

        foreach ($initialTypes as $originSlug => $packageTypes) {
            $origin = Origin::where('slug', $originSlug)->firstOrFail();
            foreach ($packageTypes as $slug => $name) {
                PackageType::create([
                    'slug' => $slug,
                    'name' => $name,
                    'origin_id' => $origin->id,
                ]);
            }
        }
    }
}
