<?php

namespace Database\Factories\WpOrg;

use App\Models\WpOrg\PluginTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PluginTagFactory extends Factory
{
    protected $model = PluginTag::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $slug = Str::slug($name);

        return [
            'id' => $this->faker->uuid(),
            'slug' => $slug,
            'name' => $name,
        ];
    }
}
