<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;

/**
 * @property-read Author|null $author
 *
 * @property-read string $id
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $version
 * @property-read string $description
 * @property-read string $download_link
 * @property-read string|null $requires
 * @property-read string|null $requires_php
 * @property-read CarbonImmutable|null $last_updated
 * @property-read CarbonImmutable|null $creation_time
 * @property-read string|null $preview_url
 * @property-read string|null $screenshot_url
 * @property-read int $rating
 * @property-read int $num_ratings
 * @property-read int $downloaded
 * @property-read int $active_installs
 * @property-read bool $is_commercial
 * @property-read bool $is_community
 * @property-read string|null $homepage
 * @property-read string|null $reviews_url
 * @property-read string|null $external_support_url
 * @property-read string|null $external_repository_url
 *
 * @property-read string $ac_origin
 * @property-read CarbonImmutable $ac_created
 * @property-read array<string, mixed> $ac_raw_metadata
 *
 * // synthesized attributes
 * @property array<string, string> $sections
 * @property array<string, string> $versions
 * @property array{"1":int, "2":int, "3":int, "4":int, "5":int} $ratings
 */
final class Theme extends BaseModel
{
    //region Definition

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
            'rating' => 'integer',
            'num_ratings' => 'integer',
            'reviews_url' => 'string',
            'downloaded' => 'integer',
            'active_installs' => 'integer',
            'homepage' => 'string',
            'is_commercial' => 'boolean',
            'external_support_url' => 'string',
            'is_community' => 'boolean',
            'external_repository_url' => 'string',
            'ac_origin' => 'string',
            'ac_created' => 'immutable_datetime',
            'ac_raw_metadata' => 'array',
        ];
    }

    /** @return BelongsToMany<ThemeTag, covariant self> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ThemeTag::class, 'theme_theme_tags', 'theme_id', 'theme_tag_id', 'id', 'id');
    }

    /** @return BelongsTo<Author, covariant self> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    //endregion

    //region Constructors

    /**
     * TODO: move to WpOrgThemeRepo
     *
     * @param array<string,mixed> $metadata
     */
    public static function fromSyncMetadata(array $metadata): self
    {
        $syncmeta = $metadata['aspiresync_meta'];
        $syncmeta['type'] === 'theme' or throw new InvalidArgumentException("invalid type '{$syncmeta['type']}'");
        $syncmeta['status'] === 'open' or throw new InvalidArgumentException("invalid status '{$syncmeta['status']}'");

        $ac_raw_metadata = $metadata;

        $authorData = $metadata['author'];
        $author = Author::firstOrCreate(['user_nicename' => $authorData['user_nicename']], $authorData);

        // TODO: use self::create for validation
        $instance = self::_create([
            'author_id' => $author->id,
            'slug' => $metadata['slug'],
            'name' => $metadata['name'],
            'description' => ($metadata['sections']['description'] ?? null) ?: null,
            'version' => $metadata['version'],
            'download_link' => $metadata['download_link'],
            'requires' => ($metadata['requires'] ?? null) ?: null,
            'requires_php' => ($metadata['requires_php'] ?? null) ?: null,
            'last_updated' => Carbon::parse($metadata['last_updated_time']),
            'creation_time' => Carbon::parse($metadata['creation_time']),
            'preview_url' => ($metadata['preview_url'] ?? null) ?: null,
            'screenshot_url' => ($metadata['screenshot_url'] ?? null) ?: null,
            'rating' => $metadata['rating'] ?? 0,
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'reviews_url' => ($metadata['reviews_url'] ?? null) ?: null,
            'downloaded' => $metadata['downloaded'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'homepage' => ($metadata['homepage'] ?? null) ?: null,
            'is_commercial' => ($metadata['is_commercial'] ?? null) ?: false,
            'external_support_url' => ($metadata['external_support_url'] ?? null) ?: null,
            'is_community' => ($metadata['is_community'] ?? null) ?: false,
            'external_repository_url' => ($metadata['external_repository_url'] ?? null) ?: null,
            'ac_origin' => $syncmeta['origin'],
            'ac_raw_metadata' => $ac_raw_metadata,
        ]);

        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            $instance->addTags($metadata['tags']);
        }
        return $instance;
    }

    //endregion

    //region Attributes
    // TODO: tighten up getter types in generics

    /** @return Attribute<array{"1":int, "2":int, "3":int, "4":int, "5":int}, never> */
    public function ratings(): Attribute
    {
        return $this->_arrayAccessor('ratings');
    }

    /** @return Attribute<array<string, string>, never> */
    public function sections(): Attribute
    {
        return $this->_arrayAccessor('sections');
    }

    /** @return Attribute<array<string, string>, never> */
    public function versions(): Attribute
    {
        return $this->_arrayAccessor('versions');
    }

    /// private api

    private function _arrayAccessor(string $name): Attribute
    {
        return Attribute::make(
            get: fn() => $this->getMetadataArray($name),
            set: fn() => throw new InvalidArgumentException("Cannot modify read-only property '$name'"),
        );
    }

    /** @return array<array-key, mixed> */
    private function getMetadataArray(string $field): array
    {
        return ($this->ac_raw_metadata[$field] ?? []) ?: [];    // coerce false into an array
    }

    //endregion

    //region Collection Management

    /** @param array<string, string> $tags */
    public function addTags(array $tags): self
    {
        $themeTags = [];
        foreach ($tags as $tagSlug => $name) {
            $themeTags[] = ThemeTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $name]);
        }
        $this->tags()->saveMany($themeTags);
        return $this;
    }

    /** @param string[] $tagSlugs */
    public function addTagsBySlugs(array $tagSlugs): self
    {
        return $this->addTags(array_combine($tagSlugs, $tagSlugs));
    }

    /** @return array<string, string> */
    public function tagsArray(): array
    {
        return $this->tags()->select('name', 'slug')->pluck('name', 'slug')->toArray();
    }

    //endregion
}
