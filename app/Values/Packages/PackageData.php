<?php

namespace App\Values\Packages;

use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use App\Values\DTO;
use Bag\Attributes\Transforms;

readonly class PackageData extends DTO
{
    /**
     * @param array<string, mixed> $raw_metadata
     */
    public function __construct(
        public string $did,
        public string $name,
        public string $slug,
        public string $description,
        public string $download_url,
        public string $version,
        public string $type,
        public string $origin,
        public array $raw_metadata = [],
    ) {}

    /**
     * Transforms FairMetadata to a package data array.
     *
     * @param FairMetadata $fairMetadata
     * @return array<string, mixed>
     */
    #[Transforms(FairMetadata::class)]
    public static function fromFairMetadata(FairMetadata $fairMetadata): array
    {
        $releases = $fairMetadata->releases;
        $release = end($releases);
        $version = $release['version'];
        $downloadUrl = $release['artifacts']['package'][0]['url'];

        $type = match ($fairMetadata->type) {
            'wp-plugin' => 'plugin',
            'wp-theme' => 'theme',
            default => throw new \InvalidArgumentException('Unsupported type: ' . $fairMetadata->type),
        };

        return [
            'did' => $fairMetadata->id,
            'type' => $type,
            'origin' => 'fair',
            'slug' => $fairMetadata->slug,
            'name' => $fairMetadata->name,
            'description' => $fairMetadata->description,
            'download_url' => $downloadUrl,
            'version' => $version,
            'raw_metadata' => $fairMetadata->raw_metadata,
        ];
    }

    /**
     * @param Plugin $plugin
     * @return array<string, mixed>
     */
    #[Transforms(Plugin::class)]
    public static function fromPlugin(Plugin $plugin): array
    {
        return [
            'did' => 'fake:' . $plugin->slug, // @todo - generate a real DID
            'type' => 'plugin',
            'origin' => 'wp_org',
            'slug' => $plugin->slug,
            'name' => $plugin->name,
            'description' => $plugin->description,
            'download_url' => $plugin->download_link,
            'version' => $plugin->version,
            'raw_metadata' => $plugin->ac_raw_metadata,
        ];
    }

    /**
     * @param Theme $theme
     * @return array<string, mixed>
     */
    #[Transforms(Theme::class)]
    public static function fromTheme(Theme $theme): array
    {
        return [
            'did' => 'fake:' . $theme->slug, // @todo - generate a real DID
            'type' => 'theme',
            'origin' => 'wp_org',
            'slug' => $theme->slug,
            'name' => $theme->name,
            'description' => $theme->description,
            'download_url' => $theme->download_link,
            'version' => $theme->version,
            'raw_metadata' => $theme->ac_raw_metadata,
        ];
    }

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'did' => ['required', 'string'],
            'type' => ['required', 'string'],
            'slug' => ['nullable', 'string'],
            'name' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'download_url' => ['required', 'string'],
            'version' => ['required', 'string'],
            'origin' => ['required', 'string'],
            'raw_metadata' => ['required', 'array'],
        ];
    }
}
