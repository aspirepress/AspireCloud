<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\Author;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

// WORK IN PROGRESS: not used yet.  many fields need to be made nullable or Optional.

class ThemeResponse extends Data
{
    /**
     * @param string $name
     * @param string $slug
     * @param string $version
     * @param string $preview_url
     * @param Author $author
     * @param string $screenshot_url
     * @param array{1:int, 2:int, 3:int, 4:int, 5:int} $ratings
     * @param int $rating
     * @param int $num_ratings
     * @param string $reviews_url
     * @param int $downloaded
     * @param int $active_installs
     * @param CarbonImmutable $last_updated
     * @param CarbonImmutable $last_updated_time
     * @param CarbonImmutable $creation_time
     * @param string $homepage
     * @param array<string,string> $sections
     * @param string $download_link
     * @param array<string,string> $tags
     * @param array<string,string> $versions
     * @param bool $requires
     * @param string $requires_php
     * @param bool $is_commercial
     * @param string|bool $external_support_url
     * @param bool $is_community
     * @param string $external_repository_url
     */
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
