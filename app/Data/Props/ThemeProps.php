<?php

declare(strict_types=1);

namespace App\Data\Props;

use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\Validation\Uuid;
use Spatie\LaravelData\Optional;

class ThemeProps extends ModelProps
{
    public function __construct(
        #[Uuid]
        public readonly Optional|string $id,

        public readonly string $slug,
        public readonly string $name,
        public readonly Optional|string $description, // FIXME: should not be optional!
        public readonly string $version,
        public readonly string $download_link,
        public readonly Optional|string|null $requires_php,

        public readonly Optional|CarbonImmutable $last_updated,
        public readonly Optional|CarbonImmutable $creation_time,

        public readonly Optional|string|null $preview_url,
        public readonly Optional|string|null $screenshot_url,
        public readonly Optional|array|null $ratings,
        public readonly Optional|int $rating,
        public readonly Optional|int $num_ratings,
        public readonly Optional|string|null $reviews_url,
        public readonly Optional|int $downloaded,
        public readonly Optional|int $active_installs,
        public readonly Optional|string|null $homepage,
        public readonly Optional|array|null $sections,
        public readonly Optional|array|null $versions,
        public readonly Optional|array|null $requires,
        public readonly Optional|bool $is_commercial,
        public readonly Optional|string|null $external_support_url,
        public readonly Optional|bool $is_community,
        public readonly Optional|string|null $external_repository_url,

        // associations
        public readonly Optional|Author $author,
        public readonly Optional|string $author_id,
        public readonly Optional|array $tags, // TODO (drop the column in the db too!)

        // AC-specific
        public readonly Optional|CarbonImmutable|null $ac_created,
        public readonly Optional|array|null $ac_raw_metadata,
    ) {}
}
