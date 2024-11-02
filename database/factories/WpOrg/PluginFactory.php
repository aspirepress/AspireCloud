<?php

namespace Database\Factories\WpOrg;

use App\Models\Sync\SyncPlugin;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\PluginTag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PluginFactory extends Factory
{
    protected $model = Plugin::class;

    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $slug = Str::slug($name);

        return [
            'id' => $this->faker->uuid(),
            'sync_id' => SyncPlugin::factory(),
            'slug' => $slug,
            'name' => $name,
            'short_description' => $this->faker->sentence(10),
            'description' => $this->faker->paragraphs(3, true),
            'version' => $this->faker->semver(),
            'author' => $this->faker->name(),
            'requires' => 'WordPress ' . $this->faker->semver(),
            'requires_php' => '>=' . $this->faker->numberBetween(7, 8) . '.0',
            'tested' => 'WordPress ' . $this->faker->semver(),
            'download_link' => $this->faker->url(),
            'added' => $this->faker->dateTimeBetween('-2 years'),
            'last_updated' => $this->faker->dateTimeBetween('-6 months'),
            'author_profile' => $this->faker->url(),
            'rating' => $this->faker->numberBetween(0, 5),
            'ratings' => [
                '5' => $this->faker->numberBetween(0, 100),
                '4' => $this->faker->numberBetween(0, 80),
                '3' => $this->faker->numberBetween(0, 60),
                '2' => $this->faker->numberBetween(0, 40),
                '1' => $this->faker->numberBetween(0, 20),
            ],
            'num_ratings' => $this->faker->numberBetween(0, 1000),
            'support_threads' => $this->faker->numberBetween(0, 100),
            'support_threads_resolved' => $this->faker->numberBetween(0, 50),
            'active_installs' => $this->faker->numberBetween(0, 1000000),
            'downloaded' => $this->faker->numberBetween(0, 2000000),
            'homepage' => $this->faker->url(),
            'banners' => [
                'low' => $this->faker->imageUrl(772, 250),
                'high' => $this->faker->imageUrl(1544, 500),
            ],
            'donate_link' => $this->faker->optional()->url(),
            'contributors' => $this->generateContributors(),
            'icons' => [
                '1x' => $this->faker->imageUrl(128, 128),
                '2x' => $this->faker->imageUrl(256, 256),
            ],
            'source' => [
                'type' => $this->faker->randomElement(['wordpress.org', 'github', 'bitbucket']),
                'url' => $this->faker->url(),
            ],
            'business_model' => $this->faker->randomElement(['freemium', 'free', 'premium']),
            'commercial_support_url' => $this->faker->optional()->url(),
            'support_url' => $this->faker->url(),
            'preview_link' => $this->faker->url(),
            'repository_url' => $this->faker->url(),
            'requires_plugins' => $this->generateRequiredPlugins(),
            'compatibility' => $this->generateCompatibility(),
            'screenshots' => $this->generateScreenshots(),
            'sections' => $this->generateSections(),
            'versions' => $this->generateVersions(),
            'upgrade_notice' => $this->generateUpgradeNotices(),
        ];
    }

    protected function generateContributors(): array
    {
        $contributors = [];
        $count = $this->faker->numberBetween(1, 5);

        for ($i = 0; $i < $count; $i++) {
            $username = $this->faker->userName();
            $contributors[$username] = [
                'profile' => "https://profiles.wordpress.org/{$username}",
                'avatar' => $this->faker->imageUrl(96, 96),
                'display_name' => $this->faker->name(),
            ];
        }

        return $contributors;
    }

    protected function generateRequiredPlugins(): array
    {
        $plugins = [];
        if ($this->faker->boolean(30)) {
            $count = $this->faker->numberBetween(1, 3);
            for ($i = 0; $i < $count; $i++) {
                $plugins[] = [
                    'name' => $this->faker->words(2, true),
                    'slug' => $this->faker->slug(),
                    'version' => $this->faker->semver(),
                ];
            }
        }
        return $plugins;
    }

    protected function generateCompatibility(): array
    {
        return [
            'wordpress' => [
                'minimum' => $this->faker->semver(),
                'maximum' => $this->faker->semver(),
                'tested' => $this->faker->semver(),
            ],
            'php' => [
                'minimum' => $this->faker->randomElement(['7.2', '7.4', '8.0', '8.1']),
                'recommended' => $this->faker->randomElement(['7.4', '8.0', '8.1', '8.2']),
            ],
        ];
    }

    protected function generateScreenshots(): array
    {
        $screenshots = [];
        $count = $this->faker->numberBetween(2, 6);

        for ($i = 0; $i < $count; $i++) {
            $screenshots[] = [
                'url' => $this->faker->imageUrl(1280, 720),
                'caption' => $this->faker->sentence(),
            ];
        }

        return $screenshots;
    }

    protected function generateSections(): array
    {
        return [
            'description' => $this->faker->paragraphs(3, true),
            'installation' => $this->faker->paragraphs(2, true),
            'reviews' => $this->generateReviews(),
            'changelog' => $this->generateChangelog(),
        ];
    }

    protected function generateReviews(): string
    {
        $faq = "";
        $count = $this->faker->numberBetween(3, 6);

        for ($i = 0; $i < $count; $i++) {
            $faq .= "### " . $this->faker->sentence() . "\n\n";
            $faq .= $this->faker->paragraph() . "\n\n";
        }

        return $faq;
    }

    protected function generateChangelog(): string
    {
        $changelog = "";
        $count = $this->faker->numberBetween(3, 6);

        for ($i = $count; $i > 0; $i--) {
            $version = $this->faker->semver();
            $changelog .= "### {$version}\n";
            $changelog .= "Released: " . $this->faker->date() . "\n\n";

            $changes = $this->faker->numberBetween(2, 5);
            for ($j = 0; $j < $changes; $j++) {
                $changelog .= "* " . $this->faker->sentence() . "\n";
            }
            $changelog .= "\n";
        }

        return $changelog;
    }

    protected function generateVersions(): array
    {
        $versions = [];
        $count = $this->faker->numberBetween(3, 8);

        for ($i = 0; $i < $count; $i++) {
            $version = $this->faker->semver();
            $versions[$version] = [
                'url' => $this->faker->url(),
                'package' => $this->faker->url(),
                'requires' => 'WordPress ' . $this->faker->semver(),
                'requires_php' => '>=' . $this->faker->numberBetween(7, 8) . '.0',
                'released' => $this->faker->dateTime()->format('Y-m-d'),
            ];
        }

        return $versions;
    }

    protected function generateUpgradeNotices(): array
    {
        $notices = [];
        $count = $this->faker->numberBetween(2, 4);

        for ($i = 0; $i < $count; $i++) {
            $version = $this->faker->semver();
            $notices[$version] = $this->faker->paragraph();
        }

        return $notices;
    }

    /**
     * State for free plugins
     */
    public function free(): static
    {
        return $this->state(fn(array $attributes) => [
            'business_model' => 'free',
            'commercial_support_url' => null,
            'donate_link' => $this->faker->url(),
        ]);
    }

    /**
     * State for premium plugins
     */
    public function premium(): static
    {
        return $this->state(fn(array $attributes) => [
            'business_model' => 'premium',
            'commercial_support_url' => $this->faker->url(),
            'donate_link' => null,
        ]);
    }

    /**
     * State for highly rated plugins
     */
    public function highlyRated(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => $this->faker->numberBetween(4, 5),
            'num_ratings' => $this->faker->numberBetween(500, 1000),
            'active_installs' => $this->faker->numberBetween(100000, 1000000),
        ]);
    }

    /**
     * Configure the model factory to create a plugin with tags
     */
    public function withTags(int $count = 3): static
    {
        return $this->afterCreating(function (Plugin $plugin) use ($count) {
            $tags = PluginTag::factory()->count($count)->create();
            $plugin->tags()->attach($tags->pluck('id'));
        });
    }

    /**
     * Configure the model factory to create a plugin with specific tags
     * If tags already exist, they will be reused instead of creating duplicates
     */
    public function withSpecificTags(array $tagNames): static
    {
        return $this->afterCreating(function (Plugin $plugin) use ($tagNames) {
            $tags = collect($tagNames)->map(function ($tagName) {
                $slug = Str::slug($tagName);

                return PluginTag::query()->firstOrCreate(
                    ['slug' => $slug],
                    [
                        'id' => $this->faker->uuid(),
                        'name' => $tagName,
                    ]
                );
            });

            $plugin->tags()->attach($tags->pluck('id'));
        });
    }
}
