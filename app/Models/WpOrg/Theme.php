<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $version
 * @property string $download_link
 * @property string $requires_php
 * @property CarbonImmutable $last_updated
 * @property CarbonImmutable $creation_time
 * @property string $preview_url
 * @property string $screenshot_url
 * @property array $ratings
 * @property int $rating
 * @property int $num_ratings
 * @property string $reviews_url
 * @property int $downloaded
 * @property int $active_installs
 * @property string $homepage
 * @property array $sections
 * @property array $tags
 * @property array $versions
 * @property array $requires
 * @property bool $is_commercial
 * @property string $external_support_url
 * @property bool $is_community
 * @property string $external_repository_url
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
     * @param array<string,mixed> $metadata
     * @return $this
     */
    public static function fromSyncMetadata(array $metadata): self
    {
        $syncmeta = $metadata['aspiresync_meta'];
        $syncmeta['type'] === 'theme' or throw new InvalidArgumentException("invalid type '{$syncmeta['type']}'");
        $syncmeta['status'] === 'open' or throw new InvalidArgumentException("invalid status '{$syncmeta['status']}'");

        $authorData = $metadata['author'];
        $author = Author::firstOrCreate(['user_nicename' => $authorData['user_nicename']], $authorData);

        $trunc = fn(?string $str, int $len = 255) => ($str === null) ? null : Str::substr($str, 0, $len);

        $instance = self::create([
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
            'requires' => $metadata['requires'] ?? null,
            'is_commercial' => $metadata['is_commercial'] ?? false,
            'external_support_url' => $trunc($metadata['external_support_url'] ?? null),
            'is_community' => $metadata['is_community'] ?? false,
            'external_repository_url' => $trunc($metadata['external_repository_url'] ?? null),
        ]);

        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            $themeTags = [];
            foreach ($metadata['tags'] as $tagSlug => $name) {
                $themeTags[] = ThemeTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $trunc($name)]);
            }
            $instance->tags()->saveMany($themeTags);
        }
        return $instance;   // @phpstan-ignore-line
    }

    //endregion
}
