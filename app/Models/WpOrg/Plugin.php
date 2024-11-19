<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\PluginFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @property-read string $id
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $short_description
 * @property-read string $description
 * @property-read string $version
 * @property-read string $author
 * @property-read string $requires
 * @property-read string|null $requires_php
 * @property-read string $tested
 * @property-read string $download_link
 * @property-read CarbonImmutable $added
 * @property-read CarbonImmutable|null $last_updated
 * @property-read string|null $author_profile
 * @property-read int $rating
 * @property-read array|null $ratings
 * @property-read int $num_ratings
 * @property-read int $support_threads
 * @property-read int $support_threads_resolved
 * @property-read int $active_installs
 * @property-read int $downloaded
 * @property-read string|null $homepage
 * @property-read array|null $banners
 * @property-read string|null $donate_link
 * @property-read array|null $contributors
 * @property-read array|null $icons
 * @property-read array|null $source
 * @property-read string|null $business_model
 * @property-read string|null $commercial_support_url
 * @property-read string|null $support_url
 * @property-read string|null $preview_link
 * @property-read string|null $repository_url
 * @property-read array|null $requires_plugins
 * @property-read array|null $compatibility
 * @property-read array|null $screenshots
 * @property-read array|null $sections
 * @property-read array|null $versions
 * @property-read array|null $upgrade_notice
 * @property-read array<string, string> $tags
 */
final class Plugin extends BaseModel
{
    //region Definition

    use HasUuids;

    /** @use HasFactory<PluginFactory> */
    use HasFactory;

    protected $table = 'plugins';

    /** @phpstan-ignore-next-line */
    protected $appends = ['tags'];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'short_description' => 'string',
            'description' => 'string',
            'version' => 'string',
            'author' => 'string',
            'requires' => 'string',
            'requires_php' => 'string',
            'tested' => 'string',
            'download_link' => 'string',
            'added' => 'immutable_datetime',
            'last_updated' => 'immutable_datetime',
            'author_profile' => 'string',
            'rating' => 'integer',
            'ratings' => 'array',
            'num_ratings' => 'integer',
            'support_threads' => 'integer',
            'support_threads_resolved' => 'integer',
            'active_installs' => 'integer',
            'downloaded' => 'integer',
            'homepage' => 'string',
            'banners' => 'array',
            'tags' => 'array',
            'donate_link' => 'string',
            'contributors' => 'array',
            'icons' => 'array',
            'source' => 'array',
            'business_model' => 'string',
            'commercial_support_url' => 'string',
            'support_url' => 'string',
            'preview_link' => 'string',
            'repository_url' => 'string',
            'requires_plugins' => 'array',
            'compatibility' => 'array',
            'screenshots' => 'array',
            'sections' => 'array',
            'versions' => 'array',
            'upgrade_notice' => 'array',
            'ac_origin' => 'string',
            'ac_created' => 'datetime_immutable',
            'ac_raw_metadata' => 'array',
        ];
    }

    /** @return BelongsToMany<PluginTag, covariant self> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PluginTag::class, 'plugin_plugin_tags', 'plugin_id', 'plugin_tag_id', 'id', 'id');
    }

    //endregion

    //region Constructors

    /** @param array<string,mixed> $metadata */
    public static function fromSyncMetadata(array $metadata): self
    {
        $syncmeta = $metadata['aspiresync_meta'];
        $syncmeta['type'] === 'plugin' or throw new InvalidArgumentException("invalid type '{$syncmeta['type']}'");
        $syncmeta['status'] === 'open' or throw new InvalidArgumentException("invalid status '{$syncmeta['status']}'");

        $trunc = fn(?string $str, int $len = 255) => ($str === null) ? null : Str::substr($str, 0, $len);

        $instance = self::create([
            'slug' => $syncmeta['slug'],
            'name' => $trunc($metadata['name'] ?? ''),
            'short_description' => $trunc($metadata['short_description'] ?? '', 150),
            'description' => $metadata['description'] ?? '',
            'version' => $metadata['version'],
            'author' => $trunc($metadata['author'] ?? ''),
            'requires' => $metadata['requires'],
            'requires_php' => $metadata['requires_php'] ?? null,
            'tested' => $metadata['tested'] ?? '',
            'download_link' => $trunc($metadata['download_link'] ?? '', 1024),
            'added' => Carbon::parse($metadata['added']),
            'last_updated' => ($metadata['last_updated'] ?? null) ? Carbon::parse($metadata['last_updated']) : null,
            'author_profile' => $metadata['author_profile'] ?? null,
            'rating' => $metadata['rating'] ?? '',
            'ratings' => $metadata['ratings'] ?? null,
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'support_threads' => $metadata['support_threads'] ?? 0,
            'support_threads_resolved' => $metadata['support_threads_resolved'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'downloaded' => $metadata['downloaded'] ?? '',
            'homepage' => $metadata['homepage'] ?? null,
            'banners' => $metadata['banners'] ?? null,
            'donate_link' => $trunc($metadata['donate_link'] ?? null, 1024),
            'contributors' => $metadata['contributors'] ?? null,
            'icons' => $metadata['icons'] ?? null,
            'source' => $metadata['source'] ?? null,
            'business_model' => $metadata['business_model'] ?? null,
            'commercial_support_url' => $trunc($metadata['commercial_support_url'] ?? null, 1024),
            'support_url' => $trunc($metadata['support_url'] ?? null, 1024),
            'preview_link' => $trunc($metadata['preview_link'] ?? null, 1024),
            'repository_url' => $trunc($metadata['repository_url'] ?? null, 1024),
            'requires_plugins' => $metadata['requires_plugins'] ?? null,
            'compatibility' => $metadata['compatibility'] ?? null,
            'screenshots' => $metadata['screenshots'] ?? null,
            'sections' => $metadata['sections'] ?? null,
            'versions' => $metadata['versions'] ?? null,
            'upgrade_notice' => $metadata['upgrade_notice'] ?? null,
            'ac_origin' => $syncmeta['origin'],
            'ac_raw_metadata' => $metadata,
        ]);

        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            $pluginTags = [];
            foreach ($metadata['tags'] as $tagSlug => $name) {
                $pluginTags[] = PluginTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $name]);
            }
            $instance->tags()->saveMany($pluginTags);
        }

        return $instance;
    }

    //endregion

    /** @return array<string, string> */
    public function getTagsAttribute(): array
    {
        return $this->tags()
            ->get()
            ->pluck('name', 'slug')
            ->toArray();
    }
}
