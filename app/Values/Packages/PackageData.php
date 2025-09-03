<?php

namespace App\Values\Packages;

use App\Enums\PackageType;
use App\Enums\Origin;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use App\Values\DTO;
use Bag\Attributes\Transforms;


readonly class PackageData extends DTO
{
    /**
     * @param array<string, mixed> $raw_metadata
     * @param array<array<string, string>> $authors
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
        public array $authors = [],
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

        $authors = array_map(
            fn($author) => ['name' => $author['name'], 'url' => $author['url'] ?? null],
            $fairMetadata->authors,
        );

        return [
            'did' => $fairMetadata->id,
            'type' => $fairMetadata->type,
            'origin' => Origin::FAIR->value,
            'slug' => $fairMetadata->slug,
            'name' => $fairMetadata->name,
            'description' => $fairMetadata->description,
            'download_url' => $downloadUrl,
            'version' => $version,
            'raw_metadata' => $fairMetadata->raw_metadata,
            'authors' => $authors,
        ];
    }

    /**
     * @param Plugin $plugin
     * @return array<string, mixed>
     */
    #[Transforms(Plugin::class)]
    public static function fromPlugin(Plugin $plugin): array
    {
        if (\Safe\preg_match('/^<a href="([^"]+)">([^<]+)<\/a>$/', $plugin->author, $matches)) {
            $authorUrl = $matches[1];
            $authorName = $matches[2];
        } else {
            $authorUrl = null;
            $authorName = $plugin->author;
        }

        return [
            'did' => 'fake:' . $plugin->slug, // @todo - generate a real DID
            'type' => PackageType::PLUGIN->value,
            'origin' => Origin::WP->value,
            'slug' => $plugin->slug,
            'name' => $plugin->name,
            'description' => $plugin->description,
            'download_url' => $plugin->download_link,
            'version' => $plugin->version,
            'raw_metadata' => $plugin->ac_raw_metadata,
            'authors' => [
                [
                    'name' => $authorName,
                    'url' => $authorUrl,
                ],
            ],
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
            'type' => PackageType::THEME->value,
            'origin' => Origin::WP->value,
            'slug' => $theme->slug,
            'name' => $theme->name,
            'description' => $theme->description,
            'download_url' => $theme->download_link,
            'version' => $theme->version,
            'raw_metadata' => $theme->ac_raw_metadata,
            'authors' => [
                [
                    'name' => $theme->author->user_nicename,
                    'url' => $theme->author->author_url,
                ],
            ],
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
            'origin' => ['required', 'string', 'in:' . implode(',', Origin::values())],
            'raw_metadata' => ['required', 'array'],
        ];
    }
}
