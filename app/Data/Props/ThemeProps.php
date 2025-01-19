<?php

declare(strict_types=1);

namespace App\Data\Props;

use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\RequiredWithout;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

final class ThemeProps extends ModelProps
{
    /**
     * @param Optional|array<string, mixed>|null $ratings
     * @param Optional|array<string, mixed>|null $sections
     * @param Optional|array<string, mixed>|null $versions
     * @param Optional|array<string, mixed>|null $requires
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

        #[Between(1, 1024 * 128)]
        public readonly Optional|string $description, // FIXME: should not be optional!

        #[Between(1, 32)]
        public readonly string $version,

        #[Url]
        #[Max(1024)]
        public readonly string $download_link,

        #[Between(1, 32)]
        public readonly Optional|string|null $requires_php,

        public readonly Optional|CarbonImmutable $last_updated,

        public readonly Optional|CarbonImmutable $creation_time,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $preview_url,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $screenshot_url,

        public readonly Optional|array|null $ratings,

        #[Between(0, 100)]
        public readonly Optional|int $rating,

        #[Min(0)]
        public readonly Optional|int $num_ratings,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $reviews_url,

        #[Min(0)]
        public readonly Optional|int $downloaded,

        #[Min(0)]
        public readonly Optional|int $active_installs,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $homepage,

        public readonly Optional|array|null $sections,

        public readonly Optional|array|null $versions,

        public readonly Optional|array|null $requires,

        public readonly Optional|bool $is_commercial,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $external_support_url,

        public readonly Optional|bool $is_community,

        #[Url]
        #[Max(1024)]
        public readonly Optional|string|null $external_repository_url,

        // associations
        #[RequiredWithout('author_id')]
        public readonly Optional|Author $author,

        #[Uuid]
        #[RequiredWithout('author')]
        public readonly Optional|string $author_id,

        public readonly Optional|array $tags, // TODO (drop the column in the db too!)

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
        string $version,
        string $description,
        string $download_link,
        array $extra = [],
    ): self {
        $args = [
            ...$extra,
            ...compact(
                'slug',
                'name',
                'version',
                'description',
                'download_link',
            ),
        ];
        return self::from($args);
    }
}
