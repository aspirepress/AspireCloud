<?php

namespace Database\Factories\WpOrg;

use App\Models\WpOrg\Author;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Author> */
class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'user_nicename' => Str::slug($name),
            'profile' => $this->faker->url(),
            'avatar' => $this->faker->imageUrl(100, 100, 'people'),
            'display_name' => $name,
            'author' => $name,
            'author_url' => $this->faker->url(),
        ];
    }
}
