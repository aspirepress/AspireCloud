<?php

namespace Database\Factories\WpOrg;

use App\Models\WpOrg\ClosedPlugin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ClosedPlugin> */
class ClosedPluginFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $faker = $this->faker;
        $name = $faker->words(3, true);
        $slug = Str::slug($name);

        $reasons = [
            "author-request",
            "guideline-violation",
            "licensing-trademark-violation",
            "merged-into-core",
            "security-issue",
        ];

        return [
            'id' => $faker->uuid(),
            'slug' => $slug,
            'name' => $name,
            'description' => $faker->sentence(),
            'closed_date' => $faker->dateTimeBetween('-2 years'),
            'reason' => $faker->randomElement($reasons),
            'ac_raw_metadata' => null, // TODO
            'ac_created' => $faker->dateTimeBetween('-1 month'),
            'ac_shadow_id' => null,
        ];
    }
}
