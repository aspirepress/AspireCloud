<?php

declare(strict_types=1);

namespace App\Data\Props;

use Carbon\CarbonImmutable;
use Ramsey\Uuid\UuidInterface;
use Spatie\LaravelData\Optional;

class PluginProps extends ModelProps
{
    public function __construct(
        public readonly Optional|string $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $short_description,
        public readonly string $description,
        public readonly string $version,
        public readonly string $author,
        public readonly string $requires,
        public readonly Optional|string|null $requires_php,
        public readonly string $tested,
        public readonly string $download_link,
        public readonly CarbonImmutable $added,
        public readonly CarbonImmutable|null $last_updated,
        public readonly Optional|string|null $author_profile,
        public readonly Optional|int $rating,
        public readonly Optional|array|null $ratings,
        public readonly Optional|int $num_ratings,
        public readonly Optional|int $support_threads,
        public readonly Optional|int $support_threads_resolved,
        public readonly Optional|int $active_installs,
        public readonly Optional|int $downloaded,
        public readonly Optional|string|null $homepage,
        public readonly Optional|array|null $banners,
        public readonly Optional|string|null $donate_link,
        public readonly Optional|array|null $contributors,
        public readonly Optional|array|null $icons,
        public readonly Optional|array|null $source,
        public readonly Optional|string|null $business_model,
        public readonly Optional|string|null $commercial_support_url,
        public readonly Optional|string|null $support_url,
        public readonly Optional|string|null $preview_link,
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
        public readonly Optional|CarbonImmutable|null $ac_created,
        public readonly Optional|array|null $ac_raw_metadata,
    ) {}


}
