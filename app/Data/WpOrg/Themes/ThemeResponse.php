<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class ThemeResponse extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $version,
        public readonly string $preview_url,
        public readonly Author $author,
        public readonly string $screenshot_url,
        public readonly array $ratings, // [1 => int, 2 => int, 3 => int, 4 => int, 5 => int]
        public readonly int $rating,    // ??? between 0-100?
        public readonly int $num_ratings,
        public readonly string $reviews_url,
        public readonly int $downloaded,
        public readonly int $active_installs,
        public readonly CarbonImmutable $last_updated,
        public readonly CarbonImmutable $last_updated_time,
        public readonly CarbonImmutable $creation_time,
        public readonly string $homepage,
        public readonly array $sections,
        public readonly string $download_link,
        public readonly array $tags,
        public readonly array $versions,
        public readonly bool $requires,
        public readonly string $requires_php,
        public readonly bool $is_commercial,
        public readonly string|bool $external_support_url, // yep, actual wp.org data has it as false
        public readonly bool $is_community,
        public readonly string $external_repository_url,
    ) {}
}
