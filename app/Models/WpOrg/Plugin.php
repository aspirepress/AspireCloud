<?php

namespace App\Models\WpOrg;

use App\Data\Props\PluginProps;
use App\Models\BaseModel;
use App\Utils\Regex;
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
            'ac_created' => 'immutable_datetime',
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

    /**
     * @param PluginProps|array<string, mixed> $props
     */
    public static function create(array|PluginProps $props): self
    {
        if (is_array($props)) {
            /** @noinspection CallableParameterUseCaseInTypeContextInspection (Data::from is highly magical) */
            $props = PluginProps::from($props);
        }
        assert($props instanceof PluginProps);
        return self::_create($props->toArray());
    }

    /**
     * TODO: move to WpOrgPluginRepo
     * @param array<string,mixed> $metadata
     */
    public static function fromSyncMetadata(array $metadata): self
    {
        $syncmeta = $metadata['aspiresync_meta'];
        $syncmeta['type'] === 'plugin' or throw new InvalidArgumentException("invalid type '{$syncmeta['type']}'");
        $syncmeta['status'] === 'open' or throw new InvalidArgumentException("invalid status '{$syncmeta['status']}'");

        $ac_raw_metadata = $metadata;
        $metadata = self::rewriteMetadata($metadata);

        $trunc = fn(?string $str, int $len = 255) => ($str === null) ? null : Str::substr($str, 0, $len);

        // TODO: use self::create for validation
        $instance = self::_create([
            'slug' => $syncmeta['slug'],
            'name' => $trunc($metadata['name'] ?? ''),
            'short_description' => $trunc($metadata['short_description'] ?? '', 150),
            'description' => $metadata['description'] ?? '',
            'version' => $metadata['version'],
            'author' => $trunc($metadata['author'] ?? ''),
            'requires' => $metadata['requires'],
            'requires_php' => $metadata['requires_php'] ?: null, // use ?: to convert blank and false to null
            'tested' => $metadata['tested'] ?? '',
            'download_link' => $trunc($metadata['download_link'] ?? '', 1024),
            'added' => Carbon::parse($metadata['added']),
            'last_updated' => ($metadata['last_updated'] ?? null) ? Carbon::parse($metadata['last_updated']) : null,
            'author_profile' => $metadata['author_profile'] ?? null,
            'rating' => $metadata['rating'] ?? 0,
            'ratings' => $metadata['ratings'] ?? null,
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'support_threads' => $metadata['support_threads'] ?? 0,
            'support_threads_resolved' => $metadata['support_threads_resolved'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'downloaded' => $metadata['downloaded'] ?? '',
            'homepage' => $metadata['homepage'] ?: null,
            'banners' => $metadata['banners'] ?? null,
            'donate_link' => $trunc($metadata['donate_link'] ?: null, 1024),
            'contributors' => $metadata['contributors'] ?? null,
            'icons' => $metadata['icons'] ?? null,
            'source' => $metadata['source'] ?? null,
            'business_model' => $metadata['business_model'] ?: null,
            'commercial_support_url' => $trunc($metadata['commercial_support_url'] ?: null, 1024),
            'support_url' => $trunc($metadata['support_url'] ?: null, 1024),
            'preview_link' => $trunc($metadata['preview_link'] ?: null, 1024),
            'repository_url' => $trunc($metadata['repository_url'] ?: null, 1024),
            'requires_plugins' => $metadata['requires_plugins'] ?? null,
            'compatibility' => $metadata['compatibility'] ?? null,
            'screenshots' => $metadata['screenshots'] ?? null,
            'sections' => $metadata['sections'] ?? null,
            'versions' => $metadata['versions'] ?? null,
            'upgrade_notice' => $metadata['upgrade_notice'] ?? null,
            'ac_origin' => $syncmeta['origin'],
            'ac_raw_metadata' => $ac_raw_metadata,
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

    /**
     * @param array<string, mixed> $metadata
     * @return array<string, mixed>
     */
    public static function rewriteMetadata(array $metadata): array
    {
        if (($metadata['aspiresync_meta']['origin'] ?? '') !== 'wp_org') {
            return $metadata;
        }

        $download_link = self::rewriteDotOrgUrl($metadata['download_link'] ?? '');
        $versions = array_map(self::rewriteDotOrgUrl(...), $metadata['versions'] ?? []);
        $banners = array_map(self::rewriteDotOrgUrl(...), $metadata['banners'] ?? []);
        $icons = array_map(self::rewriteDotOrgUrl(...), $metadata['icons'] ?? []);

        $screenshots = array_map(
            fn(array $screenshot) => [...$screenshot, 'src' => self::rewriteDotOrgUrl($screenshot['src'] ?? '')],
            $metadata['screenshots'] ?? [],
        );

        return [...$metadata, ...compact('download_link', 'versions', 'banners', 'icons', 'screenshots')];
    }

    private static function rewriteDotOrgUrl(string $url): string
    {
        $base = config('app.aspirecloud.download.base');

        // https://downloads.wordpress.org/plugin/elementor.3.26.5.zip
        // => /download/plugin/elementor.3.26.5.zip
        if (str_contains($url, '//downloads.')) {
            return \Safe\preg_replace('#https?://.*?/#i', $base, $url);
        }

        // https://ps.w.org/elementor/assets/screenshot-1.gif?rev=3005087
        // => /download/assets/plugin/elementor/3005087/screenshot-1.gif
        if ($matches = Regex::match('#//ps\.w\.org/(.*?)/assets/(.*?)(?:\?rev=(.*))?$#i', $url)) {
            $slug = $matches[1];
            $file = $matches[2];
            $revision = $matches[3] ?? 'head';
            return $base . "assets/plugin/$slug/$revision/$file";
        }

        // https://s.w.org/plugins/geopattern-icon/addi-simple-slider_c8bcb2.svg
        // => /download/gp-icon/plugin/addi-simple-slider/head/addi-simple-slider_c8bcb2.svg
        if ($matches = Regex::match(
            '#//s\.w\.org/plugins/geopattern-icon/((.*?)(?:_[^.]+)?\.svg)(?:\?rev=(.*))?$#i',
            $url,
        )) {
            $file = $matches[1];
            $slug = $matches[2];
            $revision = $matches[3] ?? 'head';
            return $base . "gp-icon/plugin/$slug/$revision/$file";
        }

        return $url;
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
