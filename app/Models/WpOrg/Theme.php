<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use App\Models\Sync\SyncTheme;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
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

    /** @return BelongsTo<SyncTheme, covariant self> */
    public function syncTheme(): BelongsTo
    {
        return $this->belongsTo(SyncTheme::class, 'sync_id', 'id');
    }

    //endregion

    //region Constructors

    public static function getOrCreateFromSyncTheme(SyncTheme $syncTheme): self
    {
        return self::where('sync_id', $syncTheme->id)->first() ?? self::createFromSyncTheme($syncTheme);
    }

    public static function createFromSyncTheme(SyncTheme $syncTheme): self
    {
        $data = $syncTheme->metadata or throw new InvalidArgumentException("SyncTheme instance has no metadata");

        DB::beginTransaction();
        $authorData = $data['author'] ?? throw new InvalidArgumentException("SyncTheme metadata has no author");
        $author = Author::firstOrCreate(['user_nicename' => $authorData['user_nicename']], $authorData);

        $instance = self::create([
            'sync_id' => $syncTheme->id,
            'author_id' => $author->id,
            'slug' => $syncTheme->slug,
            'name' => $syncTheme->name,
            'version' => $syncTheme->current_version,
            'download_link' => $data['download_link'],
            'last_updated' => Carbon::parse($data['last_updated']),
            'creation_time' => Carbon::parse($data['creation_time']),
        ]);
        $instance->fillFromMetadata($data, $author);
        $instance->save();
        DB::commit();
        return $instance;
    }

    //endregion

    //region Utilities

    /**
     * @param array<string,mixed> $data
     * @return $this
     */
    public function fillFromMetadata(array $data, ?Author $author = null): self
    {
        if ($data['slug'] !== $this->slug) {
            throw new InvalidArgumentException("Metatada slug does not match [{$data['slug']} !== $this->slug]");
        }

        $authorData = $data['author'] ?? throw new InvalidArgumentException("SyncTheme metadata has no author");
        $author ??= Author::firstOrCreate(['user_nicename' => $authorData['user_nicename']], $authorData);

        if (isset($data['tags']) && is_array($data['tags'])) {
            $themeTags = [];
            $this->tags()->detach();
            foreach ($data['tags'] as $tagSlug => $name) {
                $themeTags[] = ThemeTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $name]);
            }
            $this->tags()->saveMany($themeTags);
        }

        return $this->fill([
            'author_id' => $author->id,
            'name' => $data['name'],
            'description' => $data['sections']['description'] ?? null,
            'version' => $data['version'],
            'download_link' => $data['download_link'],
            'requires_php' => $data['requires_php'] ?? null,
            'last_updated' => Carbon::parse($data['last_updated_time']),
            'creation_time' => Carbon::parse($data['creation_time']),
            // All fields below are optional
            'preview_url' => $data['preview_url'] ?? null,
            'screenshot_url' => $data['screenshot_url'] ?? null,
            'ratings' => $data['ratings'] ?? null,
            'rating' => $data['rating'] ?? 0,
            'num_ratings' => $data['num_ratings'] ?? 0,
            'reviews_url' => $data['reviews_url'] ?? null,
            'downloaded' => $data['downloaded'] ?? 0,
            'active_installs' => $data['active_installs'] ?? 0,
            'homepage' => $data['homepage'] ?? null,
            'sections' => $data['sections'] ?? null,
            'tags' => $data['tags'] ?? null,
            'versions' => $data['versions'] ?? null,
            'requires' => $data['requires'] ?? null,
            'is_commercial' => $data['is_commercial'] ?? false,
            'external_support_url' => $data['external_support_url'] ?? null,
            'is_community' => $data['is_community'] ?? false,
            'external_repository_url' => $data['external_repository_url'] ?? null,
        ]);
    }

    /** @return $this */
    public function updateFromSyncTheme(): self
    {
        return $this->fillFromMetadata($this->syncTheme->metadata);
    }
    //endregion
}
