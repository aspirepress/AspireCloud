<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Themes;

use App\Models\WpOrg\Theme;
use App\Values\DTO;
use App\Values\WpOrg\Author;
use Bag\Attributes\Hidden;
use Bag\Attributes\Transforms;
use Bag\Values\Optional;
use Illuminate\Support\Arr;

readonly class ThemeResponse extends DTO
{
    /**
     * @param Optional|array{'1': int, '2': int, '3': int, '4': int, '5': int, } $ratings
     * @param Optional|array<string, mixed> $sections
     * @param Optional|array<string, mixed> $tags
     * @param Optional|array<string, mixed> $versions
     * @param Optional|array<string, mixed> $screenshots
     * @param Optional|array<string, mixed> $photon_screenshots
     * @param Optional|array<string, mixed> $trac_tickets
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $version,
        public string|null $preview_url,
        public Optional|Author|string $author,
        public Optional|string $description,
        public Optional|string|null $screenshot_url,
        public Optional|array $ratings, // TODO: ensure this casts to array correctly
        public Optional|int $rating,
        public Optional|int $num_ratings,
        public Optional|string|null $reviews_url,
        public Optional|int $downloaded,
        public Optional|int $active_installs,
        public Optional|string|null $last_updated,
        public Optional|string|null $last_updated_time,
        public Optional|string $creation_time,
        public Optional|string|null $homepage,
        public Optional|array $sections,
        public Optional|string $download_link,
        public Optional|array $tags,
        public Optional|array $versions,
        public Optional|string|null $requires,
        public Optional|string|null $requires_php,
        public Optional|bool $is_commercial,
        public Optional|string|null $external_support_url,
        public Optional|bool $is_community,
        public Optional|string|null $external_repository_url,
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

        // AspireCloud metadata
        public Optional|string $ac_origin,
        public Optional|string $ac_created,
    ) {}

    /**
     * @return array<string, mixed>
     */
    #[Transforms(Theme::class)]
    public static function fromTheme(Theme $theme): array
    {
        return [
            'name' => $theme->name,
            'slug' => $theme->slug,
            'version' => $theme->version,
            'preview_url' => $theme->preview_url,
            'author' => $theme->author, // gets converted to $theme->author->user_nicename unless extended_author=true
            'description' => $theme->description,
            'screenshot_url' => $theme->screenshot_url,
            'ratings' => $theme->ratings,
            'rating' => $theme->rating,
            'num_ratings' => $theme->num_ratings,
            'reviews_url' => $theme->reviews_url,
            'downloaded' => $theme->downloaded,
            'active_installs' => $theme->active_installs,
            'last_updated' => $theme->last_updated?->format('Y-m-d'),
            'last_updated_time' => $theme->last_updated?->format('Y-m-d H:i:s'),
            'creation_time' => $theme->creation_time?->format('Y-m-d H:i:s'),
            'homepage' => "https://wordpress.org/themes/{$theme->slug}/",
            'sections' => $theme->sections,
            'download_link' => $theme->download_link,
            'tags' => $theme->tagsArray(),
            'versions' => $theme->versions,
            'requires' => $theme->requires,
            'requires_php' => $theme->requires_php,
            'is_commercial' => $theme->is_commercial,
            'external_support_url' => $theme->external_support_url,
            'is_community' => $theme->is_community,
            'external_repository_url' => $theme->external_repository_url,

            // hidden
            'extended_author' => $theme->author,

            // eventual support
            // 'parent' => $none,
            // 'screenshot_count' => $none,
            // 'screenshots' => $none,
            // 'theme_url' => $none,
            // 'photon_screenshots' => $none,
            // 'trac_tickets' => $none,
            // 'upload_date' => $none,

            // ac meta
            'ac_origin' => $theme->ac_origin,
            'ac_created' => $theme->ac_created,
        ];
    }

    /** @param array<string, bool> $fields */
    public function withFields(array $fields): static
    {
        $none = new Optional();
        $extendedAuthor = Arr::pull($fields, 'extended_author', false);

        $omit = collect($fields)
            ->filter(fn($val, $key) => !$val)
            ->mapWithKeys(fn(bool $val, string $key) => [$key => $none])
            ->toArray();

        $self = $this->with($omit);

        if (!$extendedAuthor && $this->author instanceof Author) {
            $self = $self->with(['author' => $this->author->user_nicename]);
        }

        return $self;
    }
}
