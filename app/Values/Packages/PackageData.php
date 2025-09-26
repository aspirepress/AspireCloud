<?php

namespace App\Values\Packages;

use App\Enums\Origin;
use App\Enums\PackageType;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use App\Values\DTO;
use Bag\Attributes\Transforms;
use App\Services\Packages\PackageDIDService;

readonly class PackageData extends DTO
{
    /**
     * @param array<string, mixed> $raw_metadata
     * @param array<array<string, string>> $authors
     * @param array<array<string, string>> $security
     * @param array<array<string, mixed>> $releases
     * @param array<string> $tags
     * @param array<string, mixed> $sections
     */
    public function __construct(
        public string $did,
        public string $name,
        public string $slug,
        public string $description,
        public string $download_url,
        public string $version,
        public string $license,
        public string $type,
        public string $origin,
        public array $raw_metadata = [],
        public array $authors = [],
        public array $security = [],
        public array $releases = [],
        public array $tags = [],
        public array $sections = [],
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

        $security = array_map(
            fn($item) => array_filter($item, fn($value) => $value !== null),
            $fairMetadata->security,
        );

        $tags = $fairMetadata->raw_metadata['keywords'] ?? [];

        $ret = [
            'did' => $fairMetadata->id,
            'type' => $fairMetadata->type,
            'origin' => Origin::FAIR->value,
            'slug' => $fairMetadata->slug,
            'name' => $fairMetadata->name,
            'description' => $fairMetadata->description,
            'download_url' => $downloadUrl,
            'version' => $version,
            'license' => $fairMetadata->license,
            'raw_metadata' => $fairMetadata->raw_metadata,
            'authors' => $authors,
            'security' => $security,
            'releases' => $releases,
            'tags' => $tags,
        ];

        if ($fairMetadata->sections) {
            $ret['sections'] = $fairMetadata->sections;
        }

        return $ret;
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

        $security = [
            [
                'url' => 'https://wordpress.org/about/security/',
            ],
        ];

        $sections = $plugin->ac_raw_metadata['sections'] ?? null;

        $releases = [
            [
                'version' => $plugin->version,
                'artifacts' => [
                    'package' => [
                        [
                            'url' => $plugin->download_link,
                        ],
                    ],
                ],
            ],
        ];

        $tags = $plugin->tags()->pluck('name')->toArray();

        $packageInfo = app()->make(PackageDIDService::class);
        $did = $packageInfo->generateWebDid(PackageType::PLUGIN->value, $plugin->slug);

        $ret = [
            'did' => $did,
            'type' => PackageType::PLUGIN->value,
            'origin' => Origin::WP->value,
            'slug' => $plugin->slug,
            'name' => $plugin->name,
            'description' => $plugin->description,
            'download_url' => $plugin->download_link,
            'version' => $plugin->version,
            'license' => $plugin->business_model === 'commercial' ? 'proprietary' : 'GPL', // @todo - proper license
            'raw_metadata' => $plugin->ac_raw_metadata,
            'authors' => [
                [
                    'name' => $authorName,
                    'url' => $authorUrl,
                ],
            ],
            'security' => $security,
            'releases' => $releases,
            'tags' => $tags,
        ];

        if ($sections) {
            $ret['sections'] = $sections;
        }

        return $ret;
    }

    /**
     * @param Theme $theme
     * @return array<string, mixed>
     */
    #[Transforms(Theme::class)]
    public static function fromTheme(Theme $theme): array
    {
        $security = [
            [
                'url' => 'https://wordpress.org/about/security/',
            ],
        ];

        $releases = [
            [
                'version' => $theme->version,
                'artifacts' => [
                    'package' => [
                        [
                            'url' => $theme->download_link,
                        ],
                    ],
                ],
            ],
        ];

        $sections = $theme->ac_raw_metadata['sections'] ?? null;

        $tags = $theme->tags()->pluck('name')->toArray();

        $packageInfo = app()->make(PackageDIDService::class);
        $did = $packageInfo->generateWebDid(PackageType::THEME->value, $theme->slug);

        $ret = [
            'did' => $did,
            'type' => PackageType::THEME->value,
            'origin' => Origin::WP->value,
            'slug' => $theme->slug,
            'name' => $theme->name,
            'description' => $theme->description,
            'download_url' => $theme->download_link,
            'version' => $theme->version,
            'license' => $theme->is_commercial ? 'proprietary' : 'GPL', // @todo - proper license
            'raw_metadata' => $theme->ac_raw_metadata,
            'authors' => [
                [
                    'name' => $theme->author->user_nicename,
                    'url' => $theme->author->author_url,
                ],
            ],
            'security' => $security,
            'releases' => $releases,
            'tags' => $tags,
        ];

        if ($sections) {
            $ret['sections'] = $sections;
        }

        return $ret;
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
            'security' => ['required', 'array'],
            'releases' => ['required', 'array'],
            'tags' => ['sometimes', 'array'],
            'sections' => ['sometimes', 'array'],
        ];
    }
}
