<?php

namespace App\Models\WpOrg;

use App\Data\Props\ThemeProps;
use App\Models\BaseModel;
use App\Utils\Regex;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @property-read string $id
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $version
 * @property-read string $download_link
 * @property-read string $requires_php
 * @property-read CarbonImmutable $last_updated
 * @property-read CarbonImmutable $creation_time
 * @property-read string $preview_url
 * @property-read string $screenshot_url
 * @property-read array $ratings
 * @property-read int $rating
 * @property-read int $num_ratings
 * @property-read string $reviews_url
 * @property-read int $downloaded
 * @property-read int $active_installs
 * @property-read string $homepage
 * @property-read array $sections
 * @property-read array $tags
 * @property-read array $versions
 * @property-read array $requires
 * @property-read bool $is_commercial
 * @property-read string $external_support_url
 * @property-read bool $is_community
 * @property-read string $external_repository_url
 */
final class Theme extends BaseModel
{
    //region Model Definition

    use HasUuids;

    protected $table = 'themes';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'version' => 'string',
            'download_link' => 'string',
            'requires_php' => 'string',
            'last_updated' => 'datetime',
            'creation_time' => 'datetime',
            'preview_url' => 'string',
            'screenshot_url' => 'string',
            'ratings' => 'array',
            'rating' => 'integer',
            'num_ratings' => 'integer',
            'reviews_url' => 'string',
            'downloaded' => 'integer',
            'active_installs' => 'integer',
            'homepage' => 'string',
            'sections' => 'array',
            'tags' => 'array',
            'versions' => 'array',
            'requires' => 'array',
            'is_commercial' => 'boolean',
            'external_support_url' => 'string',
            'is_community' => 'boolean',
            'external_repository_url' => 'string',
            'ac_origin' => 'string',
            'ac_created' => 'immutable_datetime',
            'ac_raw_metadata' => 'array',
        ];
    }

    /** @return BelongsTo<Author, covariant self> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /** @return BelongsToMany<ThemeTag, covariant self> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ThemeTag::class, 'theme_theme_tags', 'theme_id', 'theme_tag_id', 'id', 'id');
    }

    //endregion

    //region Constructors

    /**
     * @param ThemeProps|array<string, mixed> $props
     */
    public static function create(array|ThemeProps $props): self
    {
        if (is_array($props)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection (Data::from is highly magical) */
            $props = ThemeProps::from($props);
        }
        assert($props instanceof ThemeProps);
        return self::_create($props->toArray());
    }

    /**
     * TODO: move to WpOrgThemeRepo
     * @param array<string,mixed> $metadata
     */
    public static function fromSyncMetadata(array $metadata): self
    {
        $syncmeta = $metadata['aspiresync_meta'];
        $syncmeta['type'] === 'theme' or throw new InvalidArgumentException("invalid type '{$syncmeta['type']}'");
        $syncmeta['status'] === 'open' or throw new InvalidArgumentException("invalid status '{$syncmeta['status']}'");

        $ac_raw_metadata = $metadata;
        $metadata = self::rewriteMetadata($metadata);

        $authorData = $metadata['author'];
        $author = Author::firstOrCreate(['user_nicename' => $authorData['user_nicename']], $authorData);

        $trunc = fn(?string $str, int $len = 255) => ($str === null) ? null : Str::substr($str, 0, $len);

        // TODO: use self::create for validation
        $instance = self::_create([
            'author_id' => $author->id,
            'slug' => $trunc($metadata['slug']),
            'name' => $trunc($metadata['name']),
            'description' => $metadata['sections']['description'] ?? null,
            'version' => $metadata['version'],
            'download_link' => $metadata['download_link'],
            'requires_php' => $metadata['requires_php'] ?? null,
            'last_updated' => Carbon::parse($metadata['last_updated_time']),
            'creation_time' => Carbon::parse($metadata['creation_time']),
            // All fields below are optional
            'preview_url' => $trunc($metadata['preview_url'] ?? null),
            'screenshot_url' => $trunc($metadata['screenshot_url'] ?? null),
            'ratings' => $metadata['ratings'] ?? null,
            'rating' => $metadata['rating'] ?? 0,
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'reviews_url' => $trunc($metadata['reviews_url'] ?? null),
            'downloaded' => $metadata['downloaded'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'homepage' => $trunc($metadata['homepage'] ?? null),
            'sections' => $metadata['sections'] ?? null,
            'tags' => $metadata['tags'] ?? null,
            'versions' => $metadata['versions'] ?? null,
            'requires' => $metadata['requires'] ?: null,
            'is_commercial' => $metadata['is_commercial'] ?? false,
            'external_support_url' => $trunc($metadata['external_support_url'] ?? null),
            'is_community' => $metadata['is_community'] ?? false,
            'external_repository_url' => $trunc($metadata['external_repository_url'] ?? null),
            'ac_origin' => $syncmeta['origin'],
            'ac_raw_metadata' => $ac_raw_metadata,
        ]);

        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            $themeTags = [];
            foreach ($metadata['tags'] as $tagSlug => $name) {
                $themeTags[] = ThemeTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $trunc($name)]);
            }
            $instance->tags()->saveMany($themeTags);
        }
        return $instance;
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public static function rewriteMetadata(array $metadata): array
    {
        if (($metadata['aspiresync_meta']['origin'] ?? '') !== 'wp_org') {
            return $metadata;
        }

        $base = config('app.url') . '/download/';
        $rewrite = fn(string $url) => \Safe\preg_replace('#https?://.*?/#i', $base, $url);

        $download_link = $rewrite($metadata['download_link'] ?? '');
        $versions = array_map($rewrite, $metadata['versions'] ?? []);

        // //ts.w.org/wp-content/themes/abhokta/screenshot.png?ver=1.0.0
        // /download/assets/theme/abhokta/1.0.0/screenshot.png
        $screenshot_url = $metadata['screenshot_url'] ?? '';
        if ($matches = Regex::match('#^.*?/themes/(.*?)/(.*?)(?:\?ver=(.*))?$#i', $screenshot_url)) {
            $slug = $matches[1];
            $file = $matches[2];
            $revision = $matches[3] ?? 'head';
            $screenshot_url = $base . "assets/theme/$slug/$revision/$file";
        }

        return [...$metadata, ...compact('download_link', 'versions', 'screenshot_url')];
    }

    //endregion
}
