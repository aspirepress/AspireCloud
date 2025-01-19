<?php

declare(strict_types=1);

namespace App\Data\Props;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

final class PluginProps extends ModelProps
{
    /**
     * @param Optional|array<string, mixed>|null $ratings
     * @param Optional|array<string, mixed>|null $banners
     * @param Optional|array<string, mixed>|null $contributors
     * @param Optional|array<string, mixed>|null $icons
     * @param Optional|array<string, mixed>|null $source
     * @param Optional|array<string, mixed>|null $requires_plugins
     * @param Optional|array<string, mixed>|null $compatibility
     * @param Optional|array<string, mixed>|null $screenshots
     * @param Optional|array<string, mixed>|null $sections
     * @param Optional|array<string, mixed>|null $versions
     * @param Optional|array<string, mixed>|null $upgrade_notice
     * @param Optional|array<string, mixed> $tags
     * @param Optional|array<string, mixed>|null $ac_raw_metadata
     */
    public function __construct(
        #[Uuid]
        public readonly Optional|string $id,

        #[Between(1, 255)]
        public readonly string $slug,

        #[Between(1, 255)]
        public readonly string $name,

        #[Between(0, 150)]
        public readonly string $short_description,

        #[Between(1, 1024 * 128)]
        public readonly string $description,

        #[Between(1, 32)]
        public readonly string $version,

        #[Between(1, 255)]
        public readonly string $author,

        #[Between(1, 32)]
        public readonly string $requires,

        #[Between(1, 32)]
        public readonly Optional|string|null $requires_php,

        #[Between(1, 32)]
        public readonly string $tested,

        #[Url]
        #[Max(1024)]
        public readonly string $download_link,

        public readonly DateTimeInterface $added,

        public readonly ?DateTimeInterface $last_updated,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $author_profile,

        #[Between(0, 100)]
        public readonly Optional|int $rating,

        public readonly Optional|array|null $ratings,

        #[Min(0)]
        public readonly Optional|int $num_ratings,

        #[Min(0)]
        public readonly Optional|int $support_threads,

        #[Min(0)]
        public readonly Optional|int $support_threads_resolved,

        #[Min(0)]
        public readonly Optional|int $active_installs,

        #[Min(0)]
        public readonly Optional|int $downloaded,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $homepage,

        public readonly Optional|array|null $banners,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $donate_link,

        public readonly Optional|array|null $contributors,

        public readonly Optional|array|null $icons,

        public readonly Optional|array|null $source,

        #[Max(255)]
        public readonly Optional|string|null $business_model,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $commercial_support_url,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $support_url,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $preview_link,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $repository_url,

        public readonly Optional|array|null $requires_plugins,

        public readonly Optional|array|null $compatibility,

        public readonly Optional|array|null $screenshots,

        public readonly Optional|array|null $sections,

        public readonly Optional|array|null $versions,

        public readonly Optional|array|null $upgrade_notice,

        // associations
        public readonly Optional|array $tags, // TODO

        // AC-specific
        #[Between(1, 32)]
        public readonly Optional|string $ac_origin,

        public readonly Optional|CarbonImmutable|null $ac_created,

        public readonly Optional|array|null $ac_raw_metadata,
    ) {}

    /**
     * @param array<string, mixed> $extra
     */
    public static function make(
        string $slug,
        string $name,
        string $short_description,
        string $description,
        string $version,
        string $author,
        string $requires,
        string $tested,
        string $download_link,
        DateTimeInterface $added,
        ?DateTimeInterface $last_updated,
        array $extra = [],
    ): self {
        $args = [
            ...$extra,
            ...compact(
                'slug',
                'name',
                'short_description',
                'description',
                'version',
                'author',
                'requires',
                'tested',
                'download_link',
                'added',
                'last_updated',
            ),
        ];
        return self::from($args);
    }
}
