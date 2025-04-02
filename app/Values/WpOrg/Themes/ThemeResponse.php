<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use App\Values\WpOrg\Author;
use Bag\Attributes\Hidden;
use Bag\Attributes\Transforms;
use Bag\Bag;
use Bag\Values\Optional;

readonly class ThemeResponse extends Bag
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $version,
        public string $preview_url,
        public Optional|Author|string $author,
        public Optional|string $description,
        public Optional|string $screenshot_url,
        public Optional|array $ratings, // TODO: ensure this casts to array correctly
        public Optional|int $rating,
        public Optional|int $num_ratings,
        public Optional|string $reviews_url,
        public Optional|int $downloaded,
        public Optional|int $active_installs,
        public Optional|string $last_updated,
        public Optional|string $last_updated_time,
        public Optional|string $creation_time,
        public Optional|string $homepage,
        public Optional|array $sections,
        public Optional|string $download_link,
        public Optional|array $tags,
        public Optional|array $versions,
        public Optional|array $requires,
        public Optional|string $requires_php,
        public Optional|bool $is_commercial,
        public Optional|string $external_support_url,
        public Optional|bool $is_community,
        public Optional|string $external_repository_url,

        #[Hidden]
        public Optional|Author $extended_author,

        // Always empty for now
        public Optional|string $parent,
        public Optional|int $screenshot_count,
        public Optional|array $screenshots,
        public Optional|string $theme_url,
        public Optional|array $photon_screenshots,
        public Optional|array $trac_tickets,
        public Optional|string $upload_date,
    ) {}

    /**
     * @return array<string, mixed>
     * @noinspection ProperNullCoalescingOperatorUsageInspection (it's fine here)
     */
    #[Transforms(Theme::class)]
    public static function fromTheme(Theme $theme): array
    {
        // Note we fill in all fields, then strip out any not-requested Optional.  such silliness is compatibility.

        $none = new Optional();
        return [
            'name' => $theme->name,
            'slug' => $theme->slug,
            'version' => $theme->version,
            'preview_url' => $theme->preview_url,
            'author' => $theme->author ?? $none,
            // gets converted to $theme->author->user_nicename unless extended_author=true
            'description' => $theme->description ?? $none,
            'screenshot_url' => $theme->screenshot_url ?? $none,
            'ratings' => $theme->ratings ?? $none,
            'rating' => $theme->rating ?? $none,
            'num_ratings' => $theme->num_ratings ?? $none,
            'reviews_url' => $theme->reviews_url ?? $none,
            'downloaded' => $theme->downloaded ?? $none,
            'active_installs' => $theme->active_installs ?? $none,
            'last_updated' => $theme->last_updated->format('Y-m-d') ?? $none,
            'last_updated_time' => $theme->last_updated->format('Y-m-d H:i:s') ?? $none,
            'creation_time' => $theme->creation_time->format('Y-m-d H:i:s') ?? $none,
            'homepage' => "https://wordpress.org/themes/{$theme->slug}/",
            'sections' => $theme->sections ?? $none,
            'download_link' => $theme->download_link ?? $none,
            'tags' => $theme->tagsArray() ?? $none,
            'versions' => $theme->versions ?? $none,
            'requires' => $theme->requires ?? $none,
            'requires_php' => $theme->requires_php ?? $none,
            'is_commercial' => $theme->is_commercial ?? $none,
            'external_support_url' => $theme->is_commercial ? $theme->external_support_url : false ?? $none,
            'is_community' => $theme->is_community ?? $none,
            'external_repository_url' => $theme->is_community ? $theme->external_repository_url : '' ?? $none,

            // hidden
            'extended_author' => $theme->author,

            // eventual support
            'parent' => $none,
            'screenshot_count' => $none,
            'screenshots' => $none,
            'theme_url' => $none,
            'photon_screenshots' => $none,
            'trac_tickets' => $none,
            'upload_date' => $none,
        ];
    }
}
