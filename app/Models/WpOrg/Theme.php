<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use App\Utils\Regex;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\ThemeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    /** @use HasFactory<ThemeFactory> */
    use HasFactory;

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

    /** @return BelongsToMany<ThemeTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ThemeTag::class, 'theme_theme_tags', 'theme_id', 'theme_tag_id', 'id', 'id');
    }

    /** @return BelongsTo<Author, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    //endregion

    //region Constructors

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

    //region Getters

    public function getDownloadLink(): string
    {
        $orig_link = $this->attributes['download_link'] ?? '';
        if (!$this->shouldRewriteMetadata()) {
            return $orig_link;
        }

        $link = self::rewriteDotOrgUrl($orig_link);

        if (Regex::match('#/theme/([^/.]+)\.zip$#i', $link)) {
            // no dots in the filename before the extension, which means this link isn't useful for caching.
            // replace it with the url for the current version instead, or the unrewritten link if that doesn't exist.
            return $this->versions[$this->version] ?? $orig_link; // ->versions rewrites the urls itself
        }

        return $link;
    }

    public function getScreenshotUrl(): string
    {
        $url = $this->attributes['screenshot_url'] ?? '';
        if (!$this->shouldRewriteMetadata()) {
            return $url;
        }

        // //ts.w.org/wp-content/themes/abhokta/screenshot.png?ver=1.0.0
        // /download/assets/theme/abhokta/1.0.0/screenshot.png

        $base = config('app.aspirecloud.download.base');
        $matches = Regex::match('#^.*?/themes/(.*?)/(.*?)(?:\?ver=(.*))?$#i', $url);
        if (!$matches) {
            return $url;
        }
        $slug = $matches[1];
        $file = $matches[2];
        $revision = $matches[3] ?? 'head';
        return $base . "assets/theme/$slug/$revision/$file";
    }

    /** @return array{"1":int, "2":int, "3":int, "4":int, "5":int} */
    public function getRatings(): array
    {
        return $this->getMetadataArray('ratings');
    }

    /** @return array<string, string> */
    public function getSections(): array
    {
        return $this->getMetadataArray('sections');
    }

    /** @return array<string, string> */
    public function getVersions(): array
    {
        $versions = $this->getMetadataArray('versions');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $versions) : $versions;
    }

    /// private api

    /** @return array<array-key, mixed> */
    private function getMetadataArray(string $field): array
    {
        return ($this->ac_raw_metadata[$field] ?? []) ?: [];    // coerce false into an array
    }

    private function shouldRewriteMetadata(): bool
    {
        return $this->ac_origin === 'wp_org';
    }

    private static function rewriteDotOrgUrl(string $url): string
    {
        $base = config('app.aspirecloud.download.base');
        return \Safe\preg_replace('#https?://.*?/#i', $base, $url); // TODO make this check for a .org url
    }

    //endregion

    //region Attributes

    // TODO: tighten up getter types in generics

    /** @return Attribute<string, never> */
    public function downloadLink(): Attribute
    {
        // note: must be writable, since download_link appears in create()
        return Attribute::make(get: $this->getDownloadLink(...));
    }

    /** @return Attribute<array{"1":int, "2":int, "3":int, "4":int, "5":int}, never> */
    public function ratings(): Attribute
    {
        return Attribute::make(get: $this->getRatings(...), set: self::_readonly(...));
    }

    /** @return Attribute<string, never> */
    public function screenshotUrl(): Attribute
    {
        return Attribute::make(get: $this->getScreenshotUrl(...));
    }

    /** @return Attribute<array<string, string>, never> */
    public function sections(): Attribute
    {
        return Attribute::make(get: $this->getSections(...), set: self::_readonly(...));
    }

    /** @return Attribute<array<string, string>, never> */
    public function versions(): Attribute
    {
        return Attribute::make(get: $this->getVersions(...), set: self::_readonly(...));
    }

    /// private api

    private static function _readonly(): never
    {
        throw new InvalidArgumentException('Cannot modify read-only attribute');
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
