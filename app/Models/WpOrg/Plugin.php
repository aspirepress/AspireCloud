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
 * @property-read int $num_ratings
 * @property-read int $support_threads
 * @property-read int $support_threads_resolved
 * @property-read int $active_installs
 * @property-read int $downloaded
 * @property-read string|null $homepage
 * @property-read string|null $donate_link
 * @property-read string|null $business_model
 * @property-read string|null $commercial_support_url
 * @property-read string|null $support_url
 * @property-read string|null $preview_link
 * @property-read string|null $repository_url
 * @property-read string $ac_origin
 * @property-read array $ac_metadata
 */
final class Plugin extends BaseModel
{
    //region Definition

    use HasUuids;

    /** @use HasFactory<PluginFactory> */
    use HasFactory;

    protected $table = 'plugins';

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
            'num_ratings' => 'integer',
            'support_threads' => 'integer',
            'support_threads_resolved' => 'integer',
            'active_installs' => 'integer',
            'downloaded' => 'integer',
            'homepage' => 'string',
            'donate_link' => 'string',
            'business_model' => 'string',
            'commercial_support_url' => 'string',
            'support_url' => 'string',
            'preview_link' => 'string',
            'repository_url' => 'string',
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

    /** @param PluginProps|array<string, mixed> $props */
    public static function create(PluginProps|array $props): self
    {
        if (is_array($props)) {
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
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'support_threads' => $metadata['support_threads'] ?? 0,
            'support_threads_resolved' => $metadata['support_threads_resolved'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'downloaded' => $metadata['downloaded'] ?? '',
            'homepage' => $metadata['homepage'] ?: null,
            'donate_link' => $trunc($metadata['donate_link'] ?: null, 1024),
            'business_model' => $metadata['business_model'] ?: null,
            'commercial_support_url' => $trunc($metadata['commercial_support_url'] ?: null, 1024),
            'support_url' => $trunc($metadata['support_url'] ?: null, 1024),
            'preview_link' => $trunc($metadata['preview_link'] ?: null, 1024),
            'repository_url' => $trunc($metadata['repository_url'] ?: null, 1024),
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

        $download_link = self::rewriteDotOrgUrl($metadata['download_link'] ?? '');

        return [...$metadata, ...compact('download_link')];
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

    public function ratings(): array
    {
        return $this->getMetadataObject('ratings');
    }

    public function contributors(): array
    {
        return $this->getMetadataObject('contributors');
    }

    public function requires_plugins(): array
    {
        return $this->getMetadataObject('requires_plugins');
    }

    public function compatibility(): array
    {
        return $this->getMetadataObject('compatibility');
    }

    public function sections(): array
    {
        return $this->getMetadataObject('sections');
    }

    public function upgrade_notice(): array
    {
        return $this->getMetadataObject('upgrade_notice');
    }

    public function source(): array
    {
        return $this->getMetadataObject('source');
    }

    // rewritten fields

    public function banners(): array
    {
        $banners = $this->getMetadataObject('banners');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $banners) : $banners;
    }

    public function icons(): array
    {
        $icons = $this->getMetadataObject('icons');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $icons) : $icons;
    }

    public function versions(): array
    {
        $versions = $this->getMetadataObject('versions');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $versions) : $versions;
    }

    public function screenshots(): array
    {
        $screenshots = $this->getMetadataObject('screenshots');
        $rewrite = fn(array $screenshot) => [...$screenshot, 'src' => self::rewriteDotOrgUrl($screenshot['src'] ?? '')];
        return $this->shouldRewriteMetadata() ? array_map($rewrite, $screenshots) : $screenshots;
    }

    private function getMetadataObject(string $field): array
    {
        return ($this->ac_raw_metadata[$field] ?? []) ?: [];    // coerce false into an array
    }

    private function shouldRewriteMetadata(): bool
    {
        return $this->ac_origin === 'wp_org';
    }

    public function addTags(array $tags): self
    {
        $pluginTags = [];
        foreach ($tags as $tagSlug => $name) {
            $pluginTags[] = PluginTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $name]);
        }
        $this->tags()->saveMany($pluginTags);
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
}
