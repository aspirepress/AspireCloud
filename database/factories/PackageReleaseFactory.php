<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageRelease;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PackageRelease> */
class PackageReleaseFactory extends Factory
{
    protected $model = PackageRelease::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'version' => $this->faker->semver(),
            'download_url' => $this->faker->url(),
            'requires' => [
                'wp' => $this->faker->semver(),
                'php' => $this->faker->randomElement(['7.2', '7.3', '7.4', '8.0', '8.1', '8.2', '8.3']),
            ],
            'suggests' => [
                'another-plugin' => $this->faker->semver(),
            ],
            'provides' => [
                'some-feature' => $this->faker->semver(),
            ],
            'artifacts' => [
                'package' => [
                    [
                        'url' => $this->faker->url(),
                        'type' => 'zip',
                    ],
                ],
            ],
        ];
    }
}
