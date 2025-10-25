<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageTag;
use App\Models\WpOrg\Author;
use Database\Factories\PackageReleaseFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Package> */
class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        $did = 'fake:' . $this->faker->slug();
        $name = $this->faker->words(3, true);
        $slug = Str::slug($name);
        $type = $this->faker->randomElement(['wp-plugin', 'wp-theme', 'wp-core']);
        $origin = $this->faker->randomElement(['fair', 'wp']);
        $license = $this->faker->randomElement(['GPLv2', 'GPLv3', 'MIT', 'Apache-2.0', 'Proprietary']);

        return [
            'id' => $this->faker->uuid(),
            'did' => $did,
            'slug' => $slug,
            'name' => $name,
            'description' => $this->faker->paragraphs(3, true),
            'type' => $type,
            'origin' => $origin,
            'license' => $license,
            'created_at' => $this->faker->dateTimeBetween('-2 years'),
            'raw_metadata' => [],
        ];
    }

    /**
     * Configure the model factory to create a plugin with tags
     */
    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Package $package) use ($count) {
            $tags = PackageTag::factory()->count($count)->create();
            $package->tags()->attach($tags->pluck('id'));
        });
    }

    /**
     * Configure the model factory to create a package with specific tags
     * If tags already exist, they will be reused instead of creating duplicates
     */
    public function withSpecificTags(array $tagNames): static
    {
        return $this->afterCreating(function (Package $package) use ($tagNames) {
            $tags = collect($tagNames)->map(function ($tagName) {
                $slug = Str::slug($tagName);

                return PackageTag::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'id' => $this->faker->uuid(),
                        'name' => $tagName,
                    ],
                );
            });

            $package->tags()->attach($tags->pluck('id'));
        });
    }

    public function withAuthors(int $count = 1): static
    {
        return $this->afterCreating(function (Package $package) use ($count) {
            $authors = Author::factory()->count($count)->create();
            $package->authors()->attach($authors->pluck('id'));
        });
    }

    public function withReleases(int $count = 1): static
    {
        return $this->afterCreating(function (Package $package) use ($count) {
            $package
                ->releases()
                ->createMany(
                    PackageReleaseFactory::new()
                        ->count($count)
                        ->make([
                            'package_id' => $package->id,
                        ])
                        ->toArray(),
                );
        });
    }

    public function withMetas(array $metas = []): static
    {
        return $this->afterCreating(function (Package $package) use ($metas) {
            $data = [
                'metadata' => [
                    'security' => [
                        [
                            'url' => 'https://example.com/security',
                        ],
                    ],
                ],
            ];

            $data['metadata'] = array_merge($data['metadata'], $metas);

            $package->metas()->create($data);
        });
    }
}
