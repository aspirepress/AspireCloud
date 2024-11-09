<?php

namespace Database\Factories\WpOrg;

use App\Enums\AssetType;
use App\Models\WpOrg\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $assetType = $this->faker->randomElement(AssetType::cases());
        $slug = $this->faker->slug(2);
        $version = $this->faker->semver();

        return [
            'id' => Str::uuid(),
            'asset_type' => $assetType->value,
            'slug' => $slug,
            'version' => $version,
            'revision' => $this->faker->randomNumber(7),
            'upstream_path' => $this->generateUpstreamPath($assetType, $slug, $version),
            'local_path' => $this->generateLocalPath($assetType, $slug, $version),
            'repository' => 'wp_org',
        ];
    }

    /**
     * Configure the factory for plugin assets.
     */
    public function plugin(): static
    {
        return $this->state(fn(array $attributes) => [
            'asset_type' => AssetType::PLUGIN->value,
        ]);
    }

    /**
     * Configure the factory for theme assets.
     */
    public function theme(): static
    {
        return $this->state(fn(array $attributes) => [
            'asset_type' => AssetType::THEME->value,
        ]);
    }

    /**
     * Configure the factory for core WordPress assets.
     */
    public function core(): static
    {
        return $this->state(fn(array $attributes) => [
            'asset_type' => AssetType::CORE->value,
        ]);
    }

    /**
     * Configure the factory for screenshot assets.
     */
    public function screenshot(): static
    {
        return $this->state(fn(array $attributes) => [
            'asset_type' => AssetType::SCREENSHOT->value,
            'version' => null,
        ]);
    }

    /**
     * Configure the factory for banner assets.
     */
    public function banner(): static
    {
        return $this->state(fn(array $attributes) => [
            'asset_type' => AssetType::BANNER->value,
            'version' => null,
        ]);
    }

    /**
     * Generate the appropriate upstream path based on an asset type
     */
    private function generateUpstreamPath(AssetType $type, string $slug, string $version): string
    {
        return match ($type) {
            AssetType::CORE => "https://wordpress.org/wordpress-{$version}.zip",
            AssetType::PLUGIN => "https://downloads.wordpress.org/plugin/{$slug}.{$version}.zip",
            AssetType::THEME => "https://downloads.wordpress.org/theme/{$slug}.{$version}.zip",
            AssetType::SCREENSHOT => "https://ps.w.org/{$slug}/assets/screenshot-1.png",
            AssetType::BANNER => "https://ps.w.org/{$slug}/assets/banner-772x250.jpg",
        };
    }

    /**
     * Generate the appropriate local path based on asset type
     */
    private function generateLocalPath(AssetType $type, string $slug, string $version): string
    {
        return match ($type) {
            AssetType::CORE => "core/wordpress-{$version}.zip",
            AssetType::PLUGIN => "plugins/{$slug}/{$slug}.{$version}.zip",
            AssetType::THEME => "themes/{$slug}/{$slug}.{$version}.zip",
            AssetType::SCREENSHOT => "assets/{$slug}/screenshot-1.png",
            AssetType::BANNER => "assets/{$slug}/banner-772x250.jpg",
        };
    }
}
