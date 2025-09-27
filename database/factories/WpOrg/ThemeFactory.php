<?php

namespace Database\Factories\WpOrg;

use App\Models\WpOrg\Author;
use App\Models\WpOrg\Theme;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Theme> */
class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    protected array $tags = [];

    protected array $versions = [];

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $slug = Str::slug($name);

        // Generate tags.
        $this->tags = [];
        $tagCount = $this->faker->numberBetween(1, 5);
        for ($i = 0; $i < $tagCount; $i++) {
            $word = $this->faker->word();
            $this->tags[$word] = ucfirst($word); // @mago-expect analysis:mixed-array-index (faker freaks mago out)
        }

        // Generate versions.
        $this->versions = [];
        $versionCount = $this->faker->numberBetween(1, 5);
        for ($i = 0; $i < $versionCount; $i++) {
            $key = $this->faker->semver();
            $this->versions[$key] = $this->faker->url();
        }
        ksort($this->versions);

        $lastUpdatedTime = $this->faker->dateTimeBetween('-6 months');
        $origin = $this->faker->randomElement(['wp_org', 'git', 'github']);

        $author = Author::factory()->create();

        return [
            'ac_created' => $this->faker->dateTimeBetween('-1 month'),
            'ac_origin' => $origin,
            'ac_raw_metadata' => $this->setRawMetadata(...),
            'active_installs' => $this->faker->numberBetween(0, 1000000),
            'author_id' => $author->id,
            'creation_time' => $this->faker->dateTimeBetween('-1 year')->format('Y-m-d H:i:s'),
            'description' => $this->faker->paragraphs(3, true),
            'download_link' => $this->faker->url(),
            'downloaded' => $this->faker->numberBetween(0, 2000000),
            'external_repository_url' => $this->faker->url(),
            'external_support_url' => $this->faker->optional()->url(),
            'homepage' => $this->faker->url(),
            'is_commercial' => $this->faker->boolean(30),
            'is_community' => $this->faker->boolean(70),
            'last_updated' => $lastUpdatedTime->format('Y-m-d H:i:s'),
            'name' => $name,
            'num_ratings' => $this->faker->numberBetween(0, 1000),
            'preview_url' => $this->faker->url(),
            'rating' => $this->faker->numberBetween(0, 5),
            'requires_php' => $this->faker->randomElement(['7.2', '7.3', '7.4', '8.0', '8.1', '8.2', '8.3']),
            'reviews_url' => $this->faker->url(),
            'requires' => $this->faker->semver(),
            'screenshot_url' => $this->faker->imageUrl(1200, 900, 'abstract'),
            'slug' => $slug,
            'version' => array_key_last($this->versions),
        ];
    }

    /**
     * Generate the raw metadata array.
     *
     * @param array $attributes
     * @return array
     */
    private function setRawMetadata(array $attributes): array
    {
        $lastUpdated = new DateTime($attributes['last_updated']);

        return [
            'active_installs' => $attributes['active_installs'],
            'aspiresync_meta' => [
                'id' => $this->faker->uuid(),
                'name' => $attributes['name'],
                'slug' => $attributes['slug'],
                'type' => 'theme',
                'origin' => $attributes['ac_origin'],
                'pulled' => $this->faker->unixTime(),
                'status' => 'open',
                'checked' => $this->faker->unixTime(),
                'updated' => $this->faker->unixTime(),
                'version' => $attributes['version'],
            ],
            'author' => $attributes['author_id'],
            'creation_time' => $attributes['creation_time'],
            'description' => $attributes['description'],
            'download_link' => $attributes['download_link'],
            'downloaded' => $attributes['downloaded'],
            'external_repository_url' => $attributes['external_repository_url'],
            'external_support_url' => $attributes['external_support_url'],
            'homepage' => $attributes['homepage'],
            'is_commercial' => $attributes['is_commercial'],
            'is_community' => $attributes['is_community'],
            'last_updated' => $lastUpdated->format('Y-m-d'),
            'last_updated_time' => $lastUpdated->format('Y-m-d H:i:s'),
            'name' => $attributes['name'],
            'num_ratings' => $attributes['num_ratings'],
            'preview_url' => $attributes['preview_url'],
            'rating' => $attributes['rating'],
            'ratings' => [
                '5' => $this->faker->numberBetween(0, 100),
                '4' => $this->faker->numberBetween(0, 80),
                '3' => $this->faker->numberBetween(0, 60),
                '2' => $this->faker->numberBetween(0, 40),
                '1' => $this->faker->numberBetween(0, 20),
            ],
            'requires_php' => $attributes['requires_php'],
            'reviews_url' => $attributes['reviews_url'],
            'requires' => $attributes['requires'],
            'screenshot_url' => $attributes['screenshot_url'],
            'sections' => [
                'changelog' => $this->faker->paragraphs(2, true),
                'description' => $attributes['description'],
                'faq' => $this->faker->paragraphs(2, true),
                'installation' => $this->faker->paragraphs(2, true),
                'reviews' => $this->faker->paragraphs(2, true),
                'screenshots' => $this->faker->paragraphs(2, true),
            ],
            'slug' => $attributes['slug'],
            'status' => 'open',
            'tags' => $this->tags,
            'version' => $attributes['version'],
            'versions' => $this->versions,
        ];
    }
}
