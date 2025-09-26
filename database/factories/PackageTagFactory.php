<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PackageTag> */
class PackageTagFactory extends Factory
{
    protected $model = PackageTag::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
        ];
    }
}
