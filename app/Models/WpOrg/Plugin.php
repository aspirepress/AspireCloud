<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use App\Utils\Regex;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\PluginFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use InvalidArgumentException;

/**
 * @property-read string                                             $id
 * @property-read string                                             $slug
 * @property-read string                                             $name
 * @property-read string                                             $short_description
 * @property-read string                                             $description
 * @property-read string                                             $version
 * @property-read string                                             $author
 * @property-read string|null                                        $requires
 * @property-read string|null                                        $requires_php
 * @property-read string|null                                        $tested
 * @property-read string                                             $download_link
 * @property-read CarbonImmutable|null                               $added
 * @property-read CarbonImmutable|null                               $last_updated
 * @property-read string|null                                        $author_profile
 * @property-read int                                                $rating
 * @property-read int                                                $num_ratings
 * @property-read int                                                $support_threads
 * @property-read int                                                $support_threads_resolved
 * @property-read int                                                $active_installs
 * @property-read int                                                $downloaded
 * @property-read string|null                                        $homepage
 * @property-read string|null                                        $donate_link
 * @property-read string|null                                        $business_model
 * @property-read string|null                                        $commercial_support_url
 * @property-read string|null                                        $support_url
 * @property-read string|null                                        $preview_link
 * @property-read string|null                                        $repository_url
 *
 * @property-read string                                             $ac_origin
 * @property-read CarbonImmutable                                    $ac_created
 * @property-read array<string, mixed>                               $ac_raw_metadata
 *
 * // Relationships
 * @property-read Collection<int,Author>                             $contributors
 *
 * // Synthesized attributes
 * @property-read array<array-key, mixed>                            $banners       // TODO
 * @property-read array<array-key, array{src: string, caption: string}> $screenshots
 * @property-read array<string, string>                              $versions
 * @property-read array<string, string>                              $sections
 * @property-read array{"1":int, "2":int, "3":int, "4":int, "5":int} $ratings
 * @property-read string[]                                           $requires_plugins
 * @property-read array<string, string>                              $icons
 * @property-read array<array-key, mixed>                            $compatibility // TODO (it only ever seems to be empty)
 * @property-read array<string, string>                              $upgrade_notice
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

    /** @return BelongsToMany<PluginTag, $this> */
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

        // TODO: use self::create for validation
        $instance = self::_create([
            'slug' => $syncmeta['slug'],
            'name' => $metadata['name'] ?? '',
            'short_description' => $metadata['short_description'] ?? '',
            'description' => $metadata['description'] ?? '',
            'version' => $metadata['version'],
            'author' => $metadata['author'] ?? '',
            'requires' => $metadata['requires'],
            'requires_php' => ($metadata['requires_php'] ?? null) ?: null,
            'tested' => $metadata['tested'] ?? '',
            'download_link' => $metadata['download_link'] ?? '',
            'added' => ($metadata['added'] ?? null) ? Carbon::parse($metadata['added']) : null,
            'last_updated' => ($metadata['last_updated'] ?? null) ? Carbon::parse($metadata['last_updated']) : null,
            'author_profile' => $metadata['author_profile'] ?? null,
            'rating' => $metadata['rating'] ?? 0,
            'num_ratings' => $metadata['num_ratings'] ?? 0,
            'support_threads' => $metadata['support_threads'] ?? 0,
            'support_threads_resolved' => $metadata['support_threads_resolved'] ?? 0,
            'active_installs' => $metadata['active_installs'] ?? 0,
            'downloaded' => ($metadata['downloaded'] ?? 0) ?: 0,
            'homepage' => ($metadata['homepage'] ?? null) ?: null,
            'donate_link' => ($metadata['donate_link'] ?? null) ?: null,
            'business_model' => ($metadata['business_model'] ?? null) ?: null,
            'commercial_support_url' => ($metadata['commercial_support_url'] ?? null) ?: null,
            'support_url' => ($metadata['support_url'] ?? null) ?: null,
            'preview_link' => ($metadata['preview_link'] ?? null) ?: null,
            'repository_url' => ($metadata['repository_url'] ?? null) ?: null,
            'ac_origin' => $syncmeta['origin'],
            'ac_raw_metadata' => $metadata,
        ]);

        if (isset($metadata['tags']) && is_array($metadata['tags'])) {
            $instance->addTags($metadata['tags']);
        }

        if (isset($metadata['contributors']) && is_array($metadata['contributors'])) {
            $instance->addContributors($metadata['contributors']);
        }

        return $instance;
    }

    private static function rewriteDotOrgUrl(mixed $url): string
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

    //region Relationships

    /** @return BelongsToMany<Author, $this> */
    public function contributors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'plugin_authors', 'plugin_id', 'author_id', 'id', 'id');
    }

    //endregion

    //region Getters

    /** @return array<string,mixed> */
    public function getBanners(): array
    {
        $banners = $this->getMetadataArray('banners');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $banners) : $banners;
    }

    /** @return array<string,mixed> */
    public function getCompatibility(): array
    {
        return $this->getMetadataArray('compatibility');
    }

    public function getDownloadLink(): string
    {
        $orig_link = $this->attributes['download_link'] ?? '';
        if (!$this->shouldRewriteMetadata()) {
            return $orig_link;
        }

        $link = self::rewriteDotOrgUrl($orig_link);

        if (Regex::match('#/plugin/([^/.]+)\.zip$#i', $link)) {
            // no dots in the filename before the extension, which means this link isn't useful for caching.
            // replace it with the url for the current version instead, or the unrewritten link if that doesn't exist.
            return $this->versions[$this->version] ?? $orig_link; // ->versions rewrites the urls itself
        }

        return $link;
    }

    /** @return array<string,mixed> */
    public function getIcons(): array
    {
        $icons = $this->getMetadataArray('icons');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $icons) : $icons;
    }

    /** @return array<string,mixed> */
    public function getRatings(): array
    {
        return $this->getMetadataArray('ratings');
    }

    /** @return string[] */
    public function getRequiresPlugins(): array
    {
        return $this->getMetadataArray('requires_plugins');
    }

    /** @return array<string,mixed> */
    public function getScreenshots(): array
    {
        $screenshots = $this->getMetadataArray('screenshots');
        $rewrite = fn(array $screenshot) => [...$screenshot, 'src' => self::rewriteDotOrgUrl($screenshot['src'] ?? '')];
        return $this->shouldRewriteMetadata() ? array_map($rewrite, $screenshots) : $screenshots;
    }

    /** @return array<string,string> */
    public function getSections(): array
    {
        return $this->getMetadataArray('sections');
    }

    /** @return array<string,mixed> */
    public function getSource(): array
    {
        return $this->getMetadataArray('source');
    }

    /** @return array<string,mixed> */
    public function getUpgradeNotice(): array
    {
        return $this->getMetadataArray('upgrade_notice');
    }

    /** @return array<string,string> */
    public function getVersions(): array
    {
        $versions = $this->getMetadataArray('versions');
        return $this->shouldRewriteMetadata() ? array_map(self::rewriteDotOrgUrl(...), $versions) : $versions;
    }

    /// private api

    /** @return array<array-key,mixed> */
    private function getMetadataArray(string $field): array
    {
        return ($this->ac_raw_metadata[$field] ?? []) ?: []; // coerce false to empty array because lolphp and lolwp
    }

    private function shouldRewriteMetadata(): bool
    {
        return $this->ac_origin === 'wp_org';
    }

    //endregion

    //region Attributes

    // Note that Attributes are deeply magical in Laravel, and will not tolerate being subclassed or even having their
    // construction delegated to a trait.  This is about as refactored as they are going to get.

    // TODO: tighten up getter types in generics

    /** @return Attribute<array<array-key, mixed>, never> */
    public function banners(): Attribute
    {
        return Attribute::make(get: $this->getBanners(...), set: self::_readonly(...));
    }

    /** @return Attribute<array<array-key, mixed>, never> */
    public function compatibility(): Attribute
    {
        return Attribute::make(get: $this->getCompatibility(...), set: self::_readonly(...));
    }

    /** @return Attribute<string, never> (actually Attribute<string,string> but we want it to _look_ read-only) */
    public function downloadLink(): Attribute
    {
        // note: must be writable, since download_link appears in create()
        return Attribute::make(get: $this->getDownloadLink(...));
    }

    /** @return Attribute<array<array-key, mixed>, never> */
    public function icons(): Attribute
    {
        return Attribute::make(get: $this->getIcons(...), set: self::_readonly(...));
    }

    /** @return Attribute<array{"1": int, "2": int, "3": int, "4": int, "5": int}, never> */
    public function ratings(): Attribute
    {
        return Attribute::make(get: $this->getRatings(...), set: self::_readonly(...));
    }

    /** @return Attribute<string[], never> */
    public function requiresPlugins(): Attribute
    {
        return Attribute::make(get: $this->getRequiresPlugins(...), set: self::_readonly(...));
    }

    /** @return Attribute<array<array-key, mixed>, never> */
    public function screenshots(): Attribute
    {
        return Attribute::make(get: $this->getScreenshots(...), set: self::_readonly(...));
    }

    /** @return Attribute<array<string, string>, never> */
    public function sections(): Attribute
    {
        return Attribute::make(get: $this->getSections(...), set: self::_readonly(...));
    }

    /** @return Attribute<array<array-key, mixed>, never> */
    public function source(): Attribute
    {
        return Attribute::make(get: $this->getSource(...), set: self::_readonly(...));
    }

    /** @return Attribute<array<array-key, mixed>, never> */
    public function upgradeNotice(): Attribute
    {
        return Attribute::make(get: $this->getUpgradeNotice(...), set: self::_readonly(...));
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
        $pluginTags = [];
        foreach ($tags as $tagSlug => $name) {
            $pluginTags[] = PluginTag::firstOrCreate(['slug' => $tagSlug], ['slug' => $tagSlug, 'name' => $name]);
        }
        $this->tags()->saveMany($pluginTags);
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

    /** @param array<string, array<string, string>> $contributors */
    public function addContributors(array $contributors): self
    {
        $authors = [];
        foreach ($contributors as $username => $data) {
            $authors[] = Author::firstOrCreate(
                ['user_nicename' => $username],
                [
                    'display_name' => $data['display_name'] ?? $username,
                    'profile' => $data['profile'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                ],
            );
        }
        $this->contributors()->saveMany($authors);
        return $this;
    }

    //endregion
}
