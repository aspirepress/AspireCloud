<?php

namespace App\Models\WpOrg;

use App\Data\Props\ThemeProps;
use App\Models\BaseModel;
use App\Utils\Regex;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property-read int $rating
 * @property-read int $num_ratings
 * @property-read string $reviews_url
 * @property-read int $downloaded
 * @property-read int $active_installs
 * @property-read string $homepage
 * @property-read bool $is_commercial
 * @property-read string $external_support_url
 * @property-read bool $is_community
 * @property-read string $external_repository_url
 *
 * TODO
 * @property mixed $sections
 * @property mixed $versions
 * @property mixed $requires
 * @property mixed $ratings
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

    /** @param ThemeProps|array<string, mixed> $props */
    public static function create(array|ThemeProps $props): self
    {
        if (is_array($props)) {
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
            'rating' => $metadata['rating'] ?? 0,
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'reviews_url' => $trunc($metadata['reviews_url'] ?? null),
            'downloaded' => $metadata['downloaded'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'homepage' => $trunc($metadata['homepage'] ?? null),
            'is_commercial' => $metadata['is_commercial'] ?? false,
            'external_support_url' => $trunc($metadata['external_support_url'] ?? null),
            'is_community' => $metadata['is_community'] ?? false,
            'external_repository_url' => $trunc($metadata['external_repository_url'] ?? null),
            'ac_origin' => $syncmeta['origin'],
            'ac_raw_metadata' => $ac_raw_metadata,
        ]);

        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            $instance->addTags($metadata['tags']);
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

        $base = config('app.aspirecloud.download.base');
        $rewrite = fn(string $url) => \Safe\preg_replace('#https?://.*?/#i', $base, $url);

        $download_link = $rewrite($metadata['download_link'] ?? '');

        // //ts.w.org/wp-content/themes/abhokta/screenshot.png?ver=1.0.0
        // /download/assets/theme/abhokta/1.0.0/screenshot.png
        $screenshot_url = $metadata['screenshot_url'] ?? '';
        if ($matches = Regex::match('#^.*?/themes/(.*?)/(.*?)(?:\?ver=(.*))?$#i', $screenshot_url)) {
            $slug = $matches[1];
            $file = $matches[2];
            $revision = $matches[3] ?? 'head';
            $screenshot_url = $base . "assets/theme/$slug/$revision/$file";
        }

        return [...$metadata, ...compact('download_link', 'screenshot_url')];
    }

    //endregion

    //region Getters

    public function getRatings(): array
    {
        return $this->getMetadataObject('ratings');
    }

    public function getRequires(): array
    {
        return $this->getMetadataObject('requires');
    }

    public function getSections(): array
    {
        return $this->getMetadataObject('sections');
    }

    public function getVersions(): array
    {
        $versions = $this->getMetadataObject('versions');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $versions) : $versions;
    }

    /// private api

    private function getMetadataObject(string $field): array
    {
        return ($this->ac_raw_metadata[$field] ?? []) ?: [];    // coerce false into an array
    }

    private function shouldRewriteMetadata(): bool
    {
        return $this->ac_origin === 'wp_org';
    }

    private function rewriteDotOrgUrl(string $url): string
    {
        $base = config('app.aspirecloud.download.base');
        return \Safe\preg_replace('#https?://.*?/#i', $base, $url);
    }

    //endregion

    //region Attributes

    public function ratings(): Attribute
    {
        return Attribute::make(get: $this->getRatings(...), set: self::_readonly(...));
    }

    public function requires(): Attribute
    {
        return Attribute::make(get: $this->getRequires(...), set: self::_readonly(...));
    }

    public function sections(): Attribute
    {
        return Attribute::make(get: $this->getSections(...), set: self::_readonly(...));
    }

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

    public function addTags(array $tags): self
    {
        $themeTags = [];
        foreach ($tags as $tagSlug => $name) {
            $themeTags[] = ThemeTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $name]);
        }
        $this->tags()->saveMany($themeTags);
        return $this;
    }

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
