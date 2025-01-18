<?php

namespace App\Models\WpOrg;

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

        $metadata = self::rewriteMetadata($metadata);

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
        $base = config('app.url') . '/download/';

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

// addi-simple-slider	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addi-simple-slider_c8bcb2.svg""}"
// addiction-recovery-connector	"{""1x"": ""https://ps.w.org/addiction-recovery-connector/assets/icon-128x128.png?rev=2593464""}"
// addismap-elementor-element	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addismap-elementor-element.svg""}"
// additional-authors	"{""1x"": ""https://ps.w.org/additional-authors/assets/icon-128x128.png?rev=1654912"", ""2x"": ""https://ps.w.org/additional-authors/assets/icon-256x256.png?rev=1654912""}"
// addon-paypal-for-contact-form-7	"{""1x"": ""https://ps.w.org/addon-paypal-for-contact-form-7/assets/icon-128x128.png?rev=2774059"", ""2x"": ""https://ps.w.org/addon-paypal-for-contact-form-7/assets/icon-256x256.png?rev=2774059""}"
// additional-block-styles	"{""1x"": ""https://ps.w.org/additional-block-styles/assets/icon.svg?rev=2701784"", ""svg"": ""https://ps.w.org/additional-block-styles/assets/icon.svg?rev=2701784""}"
// additional-charge	"{""default"": ""https://s.w.org/plugins/geopattern-icon/additional-charge.svg""}"
// additional-charges-on-wc-checkout	"{""1x"": ""https://ps.w.org/additional-charges-on-wc-checkout/assets/icon-128x128.png?rev=3154032"", ""2x"": ""https://ps.w.org/additional-charges-on-wc-checkout/assets/icon-256x256.png?rev=3154032""}"
// additional-content	"{""1x"": ""https://ps.w.org/additional-content/assets/icon.svg?rev=1270237"", ""svg"": ""https://ps.w.org/additional-content/assets/icon.svg?rev=1270237""}"
// advanced-cookies	"{""1x"": ""https://ps.w.org/advanced-cookies/assets/icon-256x256.png?rev=2907556"", ""2x"": ""https://ps.w.org/advanced-cookies/assets/icon-256x256.png?rev=2907556""}"
// additional-email-for-order-by-category	"{""1x"": ""https://ps.w.org/additional-email-for-order-by-category/assets/icon-128x128.png?rev=1540997"", ""2x"": ""https://ps.w.org/additional-email-for-order-by-category/assets/icon-256x256.png?rev=1540997""}"
// additional-email-recipients	"{""1x"": ""https://ps.w.org/additional-email-recipients/assets/icon-256x256.png?rev=3117459"", ""2x"": ""https://ps.w.org/additional-email-recipients/assets/icon-256x256.png?rev=3117459""}"
// additional-featured-images-and-media-uploader-anywhere	"{""1x"": ""https://ps.w.org/additional-featured-images-and-media-uploader-anywhere/assets/icon-128x128.jpg?rev=2366261"", ""2x"": ""https://ps.w.org/additional-featured-images-and-media-uploader-anywhere/assets/icon-256x256.jpg?rev=2366261""}"
// advanced-forms-paypal-payment-buttons	"{""1x"": ""https://ps.w.org/advanced-forms-paypal-payment-buttons/assets/icon-128x128.png?rev=1797529"", ""2x"": ""https://ps.w.org/advanced-forms-paypal-payment-buttons/assets/icon-256x256.png?rev=1797529""}"
// advanced-visual-elements	"{""1x"": ""https://ps.w.org/advanced-visual-elements/assets/icon-128x128.png?rev=2901456"", ""2x"": ""https://ps.w.org/advanced-visual-elements/assets/icon-256x256.png?rev=2901456""}"
// additional-js	"{""1x"": ""https://ps.w.org/additional-js/assets/icon-128x128.png?rev=2149763"", ""2x"": ""https://ps.w.org/additional-js/assets/icon-256x256.png?rev=2149763""}"
// additional-fee	"{""1x"": ""https://ps.w.org/additional-fee/assets/icon-256x256.jpg?rev=1886636"", ""2x"": ""https://ps.w.org/additional-fee/assets/icon-256x256.jpg?rev=1886636""}"
// additional-measurements-units-for-woocommerce	"{""1x"": ""https://ps.w.org/additional-measurements-units-for-woocommerce/assets/icon-128x128.png?rev=2587935"", ""2x"": ""https://ps.w.org/additional-measurements-units-for-woocommerce/assets/icon-256x256.png?rev=2587935""}"
// additional-order-costs-for-woocommerce	"{""1x"": ""https://ps.w.org/additional-order-costs-for-woocommerce/assets/icon-128x128.png?rev=2344957"", ""2x"": ""https://ps.w.org/additional-order-costs-for-woocommerce/assets/icon-256x256.png?rev=2344957""}"
// additional-order-filters-for-woocommerce	"{""1x"": ""https://ps.w.org/additional-order-filters-for-woocommerce/assets/icon-128x128.jpg?rev=3068510""}"
// additional-plugins-descriptions	"{""default"": ""https://s.w.org/plugins/geopattern-icon/additional-plugins-descriptions.svg""}"
// additional-subscription-intervals	"{""default"": ""https://s.w.org/plugins/geopattern-icon/additional-subscription-intervals.svg""}"
// additional-tax-options-for-woocommerce	"{""default"": ""https://s.w.org/plugins/geopattern-icon/additional-tax-options-for-woocommerce.svg""}"
// addon-elementor-container-link	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addon-elementor-container-link.svg""}"
// additional-product-fields-for-woocommerce	"{""1x"": ""https://ps.w.org/additional-product-fields-for-woocommerce/assets/icon-128x128.jpg?rev=2786668"", ""2x"": ""https://ps.w.org/additional-product-fields-for-woocommerce/assets/icon-256x256.jpg?rev=2786668""}"
// additional-wp-tweaks-options	"{""1x"": ""https://ps.w.org/additional-wp-tweaks-options/assets/icon-128x128.png?rev=3090743""}"
// addon-custom-fee-in-cart-wc	"{""1x"": ""https://ps.w.org/addon-custom-fee-in-cart-wc/assets/icon-128x128.png?rev=2876926""}"
// addon-package-for-elementor	"{""1x"": ""https://ps.w.org/addon-package-for-elementor/assets/icon-256x256.png?rev=2395052"", ""2x"": ""https://ps.w.org/addon-package-for-elementor/assets/icon-256x256.png?rev=2395052""}"
// addon-gravityforms-sendinblue-free	"{""1x"": ""https://ps.w.org/addon-gravityforms-sendinblue-free/assets/icon.svg?rev=3087555"", ""svg"": ""https://ps.w.org/addon-gravityforms-sendinblue-free/assets/icon.svg?rev=3087555""}"
// addon-elements-for-elementor-page-builder	"{""1x"": ""https://ps.w.org/addon-elements-for-elementor-page-builder/assets/icon-128x128.png?rev=2475491"", ""2x"": ""https://ps.w.org/addon-elements-for-elementor-page-builder/assets/icon-256x256.png?rev=2475491""}"
// addon-so-widgets-bundle	"{""1x"": ""https://ps.w.org/addon-so-widgets-bundle/assets/icon-128x128.png?rev=1288199"", ""2x"": ""https://ps.w.org/addon-so-widgets-bundle/assets/icon-256x256.png?rev=1288199""}"
// addon-stripe-with-contact-form-7	"{""1x"": ""https://ps.w.org/addon-stripe-with-contact-form-7/assets/icon-128x128.png?rev=2782647"", ""2x"": ""https://ps.w.org/addon-stripe-with-contact-form-7/assets/icon-256x256.png?rev=2782647""}"
// addon-sweetalert-contact-form-7	"{""1x"": ""https://ps.w.org/addon-sweetalert-contact-form-7/assets/icon-128x128.png?rev=2279783"", ""2x"": ""https://ps.w.org/addon-sweetalert-contact-form-7/assets/icon-256x256.png?rev=2279783""}"
// addons-espania	"{""1x"": ""https://ps.w.org/addons-espania/assets/icon-128x128.jpg?rev=1649005""}"
// addonify-quick-view	"{""1x"": ""https://ps.w.org/addonify-quick-view/assets/icon-256x256.gif?rev=2958285"", ""2x"": ""https://ps.w.org/addonify-quick-view/assets/icon-256x256.gif?rev=2958285""}"
// addons-for-beaver-builder	"{""1x"": ""https://ps.w.org/addons-for-beaver-builder/assets/icon-128x128.png?rev=1902463"", ""2x"": ""https://ps.w.org/addons-for-beaver-builder/assets/icon-256x256.png?rev=1902463""}"
// addons-for-divi	"{""1x"": ""https://ps.w.org/addons-for-divi/assets/icon.svg?rev=3195181"", ""svg"": ""https://ps.w.org/addons-for-divi/assets/icon.svg?rev=3195181""}"
// addons-for-elementor	"{""1x"": ""https://ps.w.org/addons-for-elementor/assets/icon-128x128.gif?rev=2929234"", ""2x"": ""https://ps.w.org/addons-for-elementor/assets/icon-256x256.gif?rev=2929234""}"
// addons-for-elementor-builder	"{""1x"": ""https://ps.w.org/addons-for-elementor-builder/assets/icon-128x128.png?rev=3164367"", ""2x"": ""https://ps.w.org/addons-for-elementor-builder/assets/icon-256x256.png?rev=3164367""}"
// addons-for-kingcomposer	"{""1x"": ""https://ps.w.org/addons-for-kingcomposer/assets/icon-128x128.png?rev=1640533""}"
// addonse	"{""1x"": ""https://ps.w.org/addonse/assets/icon-128x128.jpg?rev=2368829"", ""2x"": ""https://ps.w.org/addonse/assets/icon-256x256.jpg?rev=2368829""}"
// arkpay	"{""1x"": ""https://ps.w.org/arkpay/assets/icon-128x128.png?rev=3079415"", ""2x"": ""https://ps.w.org/arkpay/assets/icon-256x256.png?rev=3079415""}"
// addonskit-elementor	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addonskit-elementor.svg""}"
// addons-for-visual-composer	"{""1x"": ""https://ps.w.org/addons-for-visual-composer/assets/icon-128x128.png?rev=1860549"", ""2x"": ""https://ps.w.org/addons-for-visual-composer/assets/icon-256x256.png?rev=1860549""}"
// addonsuite-for-wpadverts	"{""1x"": ""https://ps.w.org/addonsuite-for-wpadverts/assets/icon-128x128.png?rev=1956387"", ""2x"": ""https://ps.w.org/addonsuite-for-wpadverts/assets/icon-256x256.png?rev=1956388""}"
// addonskit-for-elementor	"{""1x"": ""https://ps.w.org/addonskit-for-elementor/assets/icon.svg?rev=3194002"", ""svg"": ""https://ps.w.org/addonskit-for-elementor/assets/icon.svg?rev=3194002""}"
// addpoll	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addpoll.svg""}"
// address-autocomplete-anything	"{""1x"": ""https://ps.w.org/address-autocomplete-anything/assets/icon-128x128.png?rev=2677355"", ""2x"": ""https://ps.w.org/address-autocomplete-anything/assets/icon-256x256.png?rev=2677355""}"
// addquicktag	"{""1x"": ""https://ps.w.org/addquicktag/assets/icon-256x256.png?rev=1429627"", ""2x"": ""https://ps.w.org/addquicktag/assets/icon-256x256.png?rev=1429627""}"
// address-autocomplete-contact-form-7	"{""1x"": ""https://ps.w.org/address-autocomplete-contact-form-7/assets/icon-256x256.png?rev=1860887"", ""2x"": ""https://ps.w.org/address-autocomplete-contact-form-7/assets/icon-256x256.png?rev=1860887""}"
// address-autocomplete-google-places	"{""1x"": ""https://ps.w.org/address-autocomplete-google-places/assets/icon-128x128.png?rev=2860332"", ""2x"": ""https://ps.w.org/address-autocomplete-google-places/assets/icon-256x256.png?rev=2860332""}"
// address-autocomplete-using-nextgenapi	"{""1x"": ""https://ps.w.org/address-autocomplete-using-nextgenapi/assets/icon-256x256.png?rev=2268092"", ""2x"": ""https://ps.w.org/address-autocomplete-using-nextgenapi/assets/icon-256x256.png?rev=2268092""}"
// address-bar-ads	"{""default"": ""https://s.w.org/plugins/geopattern-icon/address-bar-ads.svg""}"
// address-bar-colorizer	"{""default"": ""https://s.w.org/plugins/geopattern-icon/address-bar-colorizer.svg""}"
// address-geocoder	"{""1x"": ""https://ps.w.org/address-geocoder/assets/icon-128x128.png?rev=1400499"", ""2x"": ""https://ps.w.org/address-geocoder/assets/icon-256x256.png?rev=1400499""}"
// adwol-werbung	"{""1x"": ""https://ps.w.org/adwol-werbung/assets/icon-128x128.png?rev=1383673""}"
// adwords-conversion-tracking-code	"{""1x"": ""https://ps.w.org/adwords-conversion-tracking-code/assets/icon-128x128.png?rev=1241239""}"
// addressbar-meta-theme-color	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addressbar-meta-theme-color.svg""}"
// addressbar-theme-color	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addressbar-theme-color.svg""}"
// address-validation-address-auto-complete	"{""1x"": ""https://ps.w.org/address-validation-address-auto-complete/assets/icon-256x256.png?rev=2802383"", ""2x"": ""https://ps.w.org/address-validation-address-auto-complete/assets/icon-256x256.png?rev=2802383""}"
// addresser-autocomplete-and-address-validation	"{""1x"": ""https://ps.w.org/addresser-autocomplete-and-address-validation/assets/icon-128x128.png?rev=2841172"", ""2x"": ""https://ps.w.org/addresser-autocomplete-and-address-validation/assets/icon-256x256.png?rev=2841172""}"
// addressfinder-woo	"{""1x"": ""https://ps.w.org/addressfinder-woo/assets/icon-128x128.png?rev=2933616"", ""2x"": ""https://ps.w.org/addressfinder-woo/assets/icon-256x256.png?rev=2933616""}"
// addressian-for-woocommerce	"{""1x"": ""https://ps.w.org/addressian-for-woocommerce/assets/icon-128x128.png?rev=2218962""}"
// addresswise	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addresswise.svg""}"
// addscript	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addscript.svg""}"
// addsearch-instant-search	"{""1x"": ""https://ps.w.org/addsearch-instant-search/assets/icon.svg?rev=2234465"", ""svg"": ""https://ps.w.org/addsearch-instant-search/assets/icon.svg?rev=2234465""}"
// addstars	"{""1x"": ""https://ps.w.org/addstars/assets/icon-256x256.png?rev=2767856"", ""2x"": ""https://ps.w.org/addstars/assets/icon-256x256.png?rev=2767856""}"
// spotlight-social-photo-feeds	"{""1x"": ""https://ps.w.org/spotlight-social-photo-feeds/assets/icon-128x128.png?rev=2817710"", ""2x"": ""https://ps.w.org/spotlight-social-photo-feeds/assets/icon-256x256.png?rev=2817710""}"
// agile-crm-campaigns	"{""1x"": ""https://ps.w.org/agile-crm-campaigns/assets/icon-128x128.png?rev=1786986"", ""2x"": ""https://ps.w.org/agile-crm-campaigns/assets/icon-256x256.png?rev=1786986""}"
// agile-crm-contact-form-7-forms	"{""1x"": ""https://ps.w.org/agile-crm-contact-form-7-forms/assets/icon-128x128.png?rev=1818000"", ""2x"": ""https://ps.w.org/agile-crm-contact-form-7-forms/assets/icon-256x256.png?rev=1818000""}"
// addthis-xmlns	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addthis-xmlns.svg""}"
// addthischina	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addthischina.svg""}"
// ade-cart-manager	"{""1x"": ""https://ps.w.org/ade-cart-manager/assets/icon-256x256.gif?rev=2690832"", ""2x"": ""https://ps.w.org/ade-cart-manager/assets/icon-256x256.gif?rev=2690832""}"
// addtothis	"{""default"": ""https://s.w.org/plugins/geopattern-icon/addtothis.svg""}"
// addurilka	"{""1x"": ""https://ps.w.org/addurilka/assets/icon-128x128.png?rev=1354174"", ""2x"": ""https://ps.w.org/addurilka/assets/icon-256x256.png?rev=1354174""}"
// addweb-google-popular-post	"{""1x"": ""https://ps.w.org/addweb-google-popular-post/assets/icon.svg?rev=2547537"", ""svg"": ""https://ps.w.org/addweb-google-popular-post/assets/icon.svg?rev=2547537""}"
// addy-autocomplete-woocommerce	"{""1x"": ""https://ps.w.org/addy-autocomplete-woocommerce/assets/icon-128x128.png?rev=1822655"", ""2x"": ""https://ps.w.org/addy-autocomplete-woocommerce/assets/icon-256x256.png?rev=1822655""}"
// adec-app	"{""1x"": ""https://ps.w.org/adec-app/assets/icon-128x128.jpg?rev=1526602"", ""2x"": ""https://ps.w.org/adec-app/assets/icon-256x256.jpg?rev=1526602""}"
// adenergizer	"{""1x"": ""https://ps.w.org/adenergizer/assets/icon-128x128.jpg?rev=3167561"", ""2x"": ""https://ps.w.org/adenergizer/assets/icon-256x256.jpg?rev=3167561""}"
// adfever-for-wordpress	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adfever-for-wordpress.svg""}"
// adfever-monetisation	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adfever-monetisation.svg""}"
// a-better-wordpress-importexport	"{""default"": ""https://s.w.org/plugins/geopattern-icon/a-better-wordpress-importexport.svg""}"
// adflex-fulfillment	"{""1x"": ""https://ps.w.org/adflex-fulfillment/assets/icon-128x128.jpg?rev=2434869"", ""2x"": ""https://ps.w.org/adflex-fulfillment/assets/icon-256x256.jpg?rev=2434869""}"
// agile-crm-gravity-forms	"{""1x"": ""https://ps.w.org/agile-crm-gravity-forms/assets/icon-128x128.png?rev=1799025"", ""2x"": ""https://ps.w.org/agile-crm-gravity-forms/assets/icon-256x256.png?rev=1799025""}"
// adfly-website-monetarization	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adfly-website-monetarization.svg""}"
// adforum-oembed	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adforum-oembed.svg""}"
// adgallery-slider	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adgallery-slider.svg""}"
// adgoal-affiliate-marketing-monetization	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adgoal-affiliate-marketing-monetization.svg""}"
// adherder	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adherder.svg""}"
// beautiful-taxonomy-filters	"{""1x"": ""https://ps.w.org/beautiful-taxonomy-filters/assets/icon-128x128.png?rev=1654967"", ""2x"": ""https://ps.w.org/beautiful-taxonomy-filters/assets/icon-256x256.png?rev=1654967""}"
// cartflows	"{""1x"": ""https://ps.w.org/cartflows/assets/icon.svg?rev=2960367"", ""svg"": ""https://ps.w.org/cartflows/assets/icon.svg?rev=2960367""}"
// adjacent-archive-links	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adjacent-archive-links.svg""}"
// adiaha-hotel	"{""1x"": ""https://ps.w.org/adiaha-hotel/assets/icon-256x256.png?rev=2786180"", ""2x"": ""https://ps.w.org/adiaha-hotel/assets/icon-256x256.png?rev=2786180""}"
// adicon-server-16x16	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adicon-server-16x16.svg""}"
// newsmanapp	"{""1x"": ""https://ps.w.org/newsmanapp/assets/icon-128x128.png?rev=1207310""}"
// adilo-oembed-support	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adilo-oembed-support.svg""}"
// awesome-arrow-navigation	"{""default"": ""https://s.w.org/plugins/geopattern-icon/awesome-arrow-navigation.svg""}"
// aditum-gateway	"{""1x"": ""https://ps.w.org/aditum-gateway/assets/icon-128×128.png?rev=2656183"", ""2x"": ""https://ps.w.org/aditum-gateway/assets/icon-256×256.png?rev=2656183""}"
// qode-essential-addons	"{""1x"": ""https://ps.w.org/qode-essential-addons/assets/icon-128x128.jpg?rev=2377885"", ""2x"": ""https://ps.w.org/qode-essential-addons/assets/icon-256x256.jpg?rev=2377885""}"
// content-refresh-manager	"{""1x"": ""https://ps.w.org/content-refresh-manager/assets/icon-128x128.png?rev=3203595""}"
// moolre-payment-gateway	"{""1x"": ""https://ps.w.org/moolre-payment-gateway/assets/icon-128x128.png?rev=3161202"", ""2x"": ""https://ps.w.org/moolre-payment-gateway/assets/icon-256x256.png?rev=3161203""}"
// primer-mydata	"{""1x"": ""https://ps.w.org/primer-mydata/assets/icon-256x256.jpg?rev=2674269"", ""2x"": ""https://ps.w.org/primer-mydata/assets/icon-256x256.jpg?rev=2674269""}"
// flexible-shipping-ups	"{""1x"": ""https://ps.w.org/flexible-shipping-ups/assets/icon.svg?rev=2738069"", ""svg"": ""https://ps.w.org/flexible-shipping-ups/assets/icon.svg?rev=2738069""}"
// adjust-admin-categories	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adjust-admin-categories_33a6d8.svg""}"
// adjust-users-screen	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adjust-users-screen_844950.svg""}"
// adjusted-bounce-rate	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adjusted-bounce-rate_f8fcfd.svg""}"
// ad-maven	"{""1x"": ""https://ps.w.org/ad-maven/assets/icon-128x128.png?rev=2443169""}"
// woocommerce-xml-csv-product-import	"{""1x"": ""https://ps.w.org/woocommerce-xml-csv-product-import/assets/icon-128x128.png?rev=2570167"", ""2x"": ""https://ps.w.org/woocommerce-xml-csv-product-import/assets/icon-256x256.png?rev=2570167""}"
// adjustly-collapse	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adjustly-collapse_e3e3e3.svg""}"
// netleader-aviator	"{""1x"": ""https://ps.w.org/netleader-aviator/assets/icon-128x128.png?rev=1936984"", ""2x"": ""https://ps.w.org/netleader-aviator/assets/icon-256x256.png?rev=1936984""}"
// adjustly-nextpage	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adjustly-nextpage_d6d6d7.svg""}"
// melapress-login-security	"{""1x"": ""https://ps.w.org/melapress-login-security/assets/icon-128x128.png?rev=2961537"", ""2x"": ""https://ps.w.org/melapress-login-security/assets/icon-256x256.png?rev=2961537""}"
// docket-cache	"{""1x"": ""https://ps.w.org/docket-cache/assets/icon-128x128.png?rev=2425893"", ""2x"": ""https://ps.w.org/docket-cache/assets/icon-256x256.png?rev=2425893""}"
// query-loop-post-selector	"{""1x"": ""https://ps.w.org/query-loop-post-selector/assets/icon-128x128.png?rev=2995890"", ""2x"": ""https://ps.w.org/query-loop-post-selector/assets/icon-256x256.png?rev=2995890""}"
// adkingpro	"{""1x"": ""https://ps.w.org/adkingpro/assets/icon-128x128.png?rev=1060727"", ""2x"": ""https://ps.w.org/adkingpro/assets/icon-256x256.png?rev=1060727""}"
// adklick-advertising-management	"{""1x"": ""https://ps.w.org/adklick-advertising-management/assets/icon-128x128.png?rev=1378046"", ""2x"": ""https://ps.w.org/adklick-advertising-management/assets/icon-256x256.png?rev=1378046""}"
// agile-crm-content-management	"{""1x"": ""https://ps.w.org/agile-crm-content-management/assets/icon-128x128.png?rev=1790072"", ""2x"": ""https://ps.w.org/agile-crm-content-management/assets/icon-256x256.png?rev=1790072""}"
// sendcloud-shipping	"{""1x"": ""https://ps.w.org/sendcloud-shipping/assets/icon-128x128.png?rev=2146240"", ""2x"": ""https://ps.w.org/sendcloud-shipping/assets/icon-256x256.png?rev=2146240""}"
// sample-reviews	"{""1x"": ""https://ps.w.org/sample-reviews/assets/icon-256x256.png?rev=3026791"", ""2x"": ""https://ps.w.org/sample-reviews/assets/icon-256x256.png?rev=3026791""}"
// wpsso-commerce-manager-catalog-feed	"{""1x"": ""https://ps.w.org/wpsso-commerce-manager-catalog-feed/assets/icon-128x128.png?rev=3167709"", ""2x"": ""https://ps.w.org/wpsso-commerce-manager-catalog-feed/assets/icon-256x256.png?rev=3167709""}"
// nexs-app-embed	"{""1x"": ""https://ps.w.org/nexs-app-embed/assets/icon-128x128.png?rev=3205829""}"
// wpsso-google-merchant-feed	"{""1x"": ""https://ps.w.org/wpsso-google-merchant-feed/assets/icon-128x128.png?rev=3167712"", ""2x"": ""https://ps.w.org/wpsso-google-merchant-feed/assets/icon-256x256.png?rev=3167712""}"
// gf-excel-import	"{""1x"": ""https://ps.w.org/gf-excel-import/assets/icon-128x128.png?rev=2972723"", ""2x"": ""https://ps.w.org/gf-excel-import/assets/icon-256x256.png?rev=2972723""}"
// forumpay-crypto-payments	"{""1x"": ""https://ps.w.org/forumpay-crypto-payments/assets/icon.svg?rev=3125782"", ""svg"": ""https://ps.w.org/forumpay-crypto-payments/assets/icon.svg?rev=3125782""}"
// library-viewer	"{""1x"": ""https://ps.w.org/library-viewer/assets/icon-128x128.png?rev=2120573"", ""2x"": ""https://ps.w.org/library-viewer/assets/icon-256x256.png?rev=2120573""}"
// easy-tiktok-feed	"{""1x"": ""https://ps.w.org/easy-tiktok-feed/assets/icon-256x256.png?rev=2388565"", ""2x"": ""https://ps.w.org/easy-tiktok-feed/assets/icon-256x256.png?rev=2388565""}"
// boostrz-tag-manager	"{""default"": ""https://s.w.org/plugins/geopattern-icon/boostrz-tag-manager.svg""}"
// formbricks	"{""1x"": ""https://ps.w.org/formbricks/assets/icon-128x128.png?rev=3203201"", ""2x"": ""https://ps.w.org/formbricks/assets/icon-256x256.png?rev=3203201""}"
// vai-de-promo	"{""1x"": ""https://ps.w.org/vai-de-promo/assets/icon-128x128.png?rev=3029687"", ""2x"": ""https://ps.w.org/vai-de-promo/assets/icon-256x256.png?rev=3029687""}"
// agile-crm-email-marketing	"{""1x"": ""https://ps.w.org/agile-crm-email-marketing/assets/icon-128x128.png?rev=1792498"", ""2x"": ""https://ps.w.org/agile-crm-email-marketing/assets/icon-256x256.png?rev=1792498""}"
// agile-crm-forms	"{""1x"": ""https://ps.w.org/agile-crm-forms/assets/icon-128x128.png?rev=1792995"", ""2x"": ""https://ps.w.org/agile-crm-forms/assets/icon-256x256.png?rev=1792995""}"
// notifier-to-slack	"{""1x"": ""https://ps.w.org/notifier-to-slack/assets/icon.svg?rev=3155913"", ""svg"": ""https://ps.w.org/notifier-to-slack/assets/icon.svg?rev=3155913""}"
// wp-cafe	"{""1x"": ""https://ps.w.org/wp-cafe/assets/icon-128x128.gif?rev=2701311"", ""2x"": ""https://ps.w.org/wp-cafe/assets/icon-256x256.gif?rev=2701311""}"
// reviews-for-woocommerce	"{""1x"": ""https://ps.w.org/reviews-for-woocommerce/assets/icon-128x128.png?rev=3030074"", ""2x"": ""https://ps.w.org/reviews-for-woocommerce/assets/icon-256x256.png?rev=3030074""}"
// hootkit	"{""default"": ""https://s.w.org/plugins/geopattern-icon/hootkit.svg""}"
// html-api-debugger	"{""1x"": ""https://ps.w.org/html-api-debugger/assets/icon.svg?rev=3099293"", ""svg"": ""https://ps.w.org/html-api-debugger/assets/icon.svg?rev=3099293""}"
// snappify	"{""1x"": ""https://ps.w.org/snappify/assets/icon.svg?rev=3205885"", ""svg"": ""https://ps.w.org/snappify/assets/icon.svg?rev=3205885""}"
// members	"{""1x"": ""https://ps.w.org/members/assets/icon-128x128.png?rev=2503334"", ""2x"": ""https://ps.w.org/members/assets/icon-256x256.png?rev=2503334""}"
// dk-pdf	"{""1x"": ""https://ps.w.org/dk-pdf/assets/icon-128x128.png?rev=1643176"", ""2x"": ""https://ps.w.org/dk-pdf/assets/icon-256x256.png?rev=1643176""}"
// giveaway-lottery	"{""default"": ""https://s.w.org/plugins/geopattern-icon/giveaway-lottery.svg""}"
// list-category-posts	"{""1x"": ""https://ps.w.org/list-category-posts/assets/icon-128x128.png?rev=2517221"", ""2x"": ""https://ps.w.org/list-category-posts/assets/icon-256x256.png?rev=2517221""}"
// woo-cardknox-gateway	"{""default"": ""https://s.w.org/plugins/geopattern-icon/woo-cardknox-gateway.svg""}"
// customizer-export-import	"{""1x"": ""https://ps.w.org/customizer-export-import/assets/icon-128x128.jpg?rev=1049984"", ""2x"": ""https://ps.w.org/customizer-export-import/assets/icon-256x256.jpg?rev=1049984""}"
// speechkit	"{""1x"": ""https://ps.w.org/speechkit/assets/icon-128x128.png?rev=2633494"", ""2x"": ""https://ps.w.org/speechkit/assets/icon-256x256.png?rev=2633494""}"
// go-high-level-extension-for-gravity-form	"{""1x"": ""https://ps.w.org/go-high-level-extension-for-gravity-form/assets/icon-128x128.png?rev=3123959"", ""2x"": ""https://ps.w.org/go-high-level-extension-for-gravity-form/assets/icon-256x256.png?rev=3123959""}"
// adless	"{""1x"": ""https://ps.w.org/adless/assets/icon.svg?rev=2775936"", ""svg"": ""https://ps.w.org/adless/assets/icon.svg?rev=2775936""}"
// adlib-woo2lex-manuell	"{""1x"": ""https://ps.w.org/adlib-woo2lex-manuell/assets/icon-128x128.png?rev=1111815"", ""2x"": ""https://ps.w.org/adlib-woo2lex-manuell/assets/icon-256x256.png?rev=1111815""}"
// adm-media-list	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adm-media-list.svg""}"
// admail	"{""1x"": ""https://ps.w.org/admail/assets/icon.svg?rev=3137593"", ""svg"": ""https://ps.w.org/admail/assets/icon.svg?rev=3137593""}"
// admail-list-builder-signup-forms	"{""1x"": ""https://ps.w.org/admail-list-builder-signup-forms/assets/icon-128x128.png?rev=2180772"", ""2x"": ""https://ps.w.org/admail-list-builder-signup-forms/assets/icon-256x256.png?rev=2180772""}"
// admanage	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admanage.svg""}"
// accordion-categories	"{""default"": ""https://s.w.org/plugins/geopattern-icon/accordion-categories_4f2f21.svg""}"
// admangler	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admangler.svg""}"
// admidio-events	"{""1x"": ""https://ps.w.org/admidio-events/assets/icon-128x128.png?rev=988205"", ""2x"": ""https://ps.w.org/admidio-events/assets/icon-256x256.png?rev=988205""}"
// lc-scripts-optimizer	"{""1x"": ""https://ps.w.org/lc-scripts-optimizer/assets/icon.svg?rev=2334195"", ""svg"": ""https://ps.w.org/lc-scripts-optimizer/assets/icon.svg?rev=2334195""}"
// admin-ajax-php-no-thank-you	"{""1x"": ""https://ps.w.org/admin-ajax-php-no-thank-you/assets/icon-128x128.png?rev=1690123"", ""2x"": ""https://ps.w.org/admin-ajax-php-no-thank-you/assets/icon-256x256.png?rev=1690123""}"
// admin-ajax-search-in-backend	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-ajax-search-in-backend.svg""}"
// admin-alert-errors	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-alert-errors.svg""}"
// admin-allow-by-ip	"{""1x"": ""https://ps.w.org/admin-allow-by-ip/assets/icon-128x128.png?rev=2585895"", ""2x"": ""https://ps.w.org/admin-allow-by-ip/assets/icon-256x256.png?rev=2585895""}"
// admin-announce	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-announce.svg""}"
// admin-atlex-cloud	"{""1x"": ""https://ps.w.org/admin-atlex-cloud/assets/icon-128x128.png?rev=2061756"", ""2x"": ""https://ps.w.org/admin-atlex-cloud/assets/icon-256x256.png?rev=2061756""}"
// admin-authentication	"{""1x"": ""https://ps.w.org/admin-authentication/assets/icon.svg?rev=1694856"", ""svg"": ""https://ps.w.org/admin-authentication/assets/icon.svg?rev=1694856""}"
// admin-author-filter	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-author-filter.svg""}"
// advanced-coupon-conditions-for-woocommerce	"{""1x"": ""https://ps.w.org/advanced-coupon-conditions-for-woocommerce/assets/icon-256x256.png?rev=1539712"", ""2x"": ""https://ps.w.org/advanced-coupon-conditions-for-woocommerce/assets/icon-256x256.png?rev=1539712""}"
// admin-back-button	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-back-button.svg""}"
// admin-backend-color-coded-post-notes	"{""1x"": ""https://ps.w.org/admin-backend-color-coded-post-notes/assets/icon-128x128.png?rev=3129600"", ""2x"": ""https://ps.w.org/admin-backend-color-coded-post-notes/assets/icon-256x256.png?rev=3129600""}"
// admin-bar-addition-for-woocommerce	"{""1x"": ""https://ps.w.org/admin-bar-addition-for-woocommerce/assets/icon-128x128.png?rev=2823097"", ""2x"": ""https://ps.w.org/admin-bar-addition-for-woocommerce/assets/icon-256x256.png?rev=2823097""}"
// admin-compass	"{""1x"": ""https://ps.w.org/admin-compass/assets/icon-256x256.png?rev=3171303"", ""2x"": ""https://ps.w.org/admin-compass/assets/icon-256x256.png?rev=3171303""}"
// admin-bar	"{""1x"": ""https://ps.w.org/admin-bar/assets/icon.svg?rev=3078226"", ""svg"": ""https://ps.w.org/admin-bar/assets/icon.svg?rev=3078226""}"
// admin-bar-autohider	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-autohider.svg""}"
// admin-bar-color	"{""1x"": ""https://ps.w.org/admin-bar-color/assets/icon-128x128.png?rev=1274209"", ""2x"": ""https://ps.w.org/admin-bar-color/assets/icon-256x256.png?rev=1274209""}"
// advanced-coupon-for-woocommerce	"{""1x"": ""https://ps.w.org/advanced-coupon-for-woocommerce/assets/icon-128x128.png?rev=3121426"", ""2x"": ""https://ps.w.org/advanced-coupon-for-woocommerce/assets/icon-256x256.png?rev=3121426""}"
// admin-bar-edit-links-for-gravity-forms	"{""1x"": ""https://ps.w.org/admin-bar-edit-links-for-gravity-forms/assets/icon-128x128.png?rev=1461539"", ""2x"": ""https://ps.w.org/admin-bar-edit-links-for-gravity-forms/assets/icon-256x256.png?rev=1461539""}"
// admin-bar-edit-page-links	"{""1x"": ""https://ps.w.org/admin-bar-edit-page-links/assets/icon-128x128.png?rev=1104388"", ""2x"": ""https://ps.w.org/admin-bar-edit-page-links/assets/icon-256x256.png?rev=1104388""}"
// admin-bar-fix	"{""1x"": ""https://ps.w.org/admin-bar-fix/assets/icon.svg?rev=2752934"", ""svg"": ""https://ps.w.org/admin-bar-fix/assets/icon.svg?rev=2752934""}"
// admin-bar-dashboard-control	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-dashboard-control_1b71ad.svg""}"
// admin-bar-hide	"{""1x"": ""https://ps.w.org/admin-bar-hide/assets/icon-128x128.png?rev=1179972""}"
// admin-bar-hider	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-hider.svg""}"
// admin-bar-languages	"{""1x"": ""https://ps.w.org/admin-bar-languages/assets/icon-128x128.png?rev=1763042"", ""2x"": ""https://ps.w.org/admin-bar-languages/assets/icon-256x256.png?rev=1763042""}"
// admin-bar-menu-for-woocommerce	"{""1x"": ""https://ps.w.org/admin-bar-menu-for-woocommerce/assets/icon-256x256.jpg?rev=2507459"", ""2x"": ""https://ps.w.org/admin-bar-menu-for-woocommerce/assets/icon-256x256.jpg?rev=2507459""}"
// admin-bar-plugin-shortcut	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-plugin-shortcut_c8aca6.svg""}"
// admin-bar-plus	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-plus_404649.svg""}"
// admin-bar-position	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-position.svg""}"
// admin-bar-publish	"{""1x"": ""https://ps.w.org/admin-bar-publish/assets/icon.svg?rev=2307611"", ""svg"": ""https://ps.w.org/admin-bar-publish/assets/icon.svg?rev=2307611""}"
// advanced-control-for-gutenberg	"{""1x"": ""https://ps.w.org/advanced-control-for-gutenberg/assets/icon.svg?rev=3200173"", ""svg"": ""https://ps.w.org/advanced-control-for-gutenberg/assets/icon.svg?rev=3200173""}"
// advanced-crossword	"{""1x"": ""https://ps.w.org/advanced-crossword/assets/icon-128x128.png?rev=3027684"", ""2x"": ""https://ps.w.org/advanced-crossword/assets/icon-256x256.gif?rev=3027687""}"
// advanced-cron-manager	"{""1x"": ""https://ps.w.org/advanced-cron-manager/assets/icon.svg?rev=3096140"", ""svg"": ""https://ps.w.org/advanced-cron-manager/assets/icon.svg?rev=3096140""}"
// advanced-coupons-for-woocommerce-free	"{""1x"": ""https://ps.w.org/advanced-coupons-for-woocommerce-free/assets/icon-128x128.png?rev=2760901"", ""2x"": ""https://ps.w.org/advanced-coupons-for-woocommerce-free/assets/icon-256x256.png?rev=2760901""}"
// admin-bar-queries	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-queries.svg""}"
// admin-bar-theme-switcher	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-theme-switcher.svg""}"
// admin-bar-toggle	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-bar-toggle_00539e.svg""}"
// admin-big-width	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-big-width.svg""}"
// admin-bar-wrap-fix	"{""1x"": ""https://ps.w.org/admin-bar-wrap-fix/assets/icon-128x128.png?rev=1924892"", ""2x"": ""https://ps.w.org/admin-bar-wrap-fix/assets/icon-256x256.png?rev=1924892""}"
// admin-category-filter	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-category-filter_cae27e.svg""}"
// admin-category-search	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-category-search.svg""}"
// paygate-payweb-for-woocommerce	"{""1x"": ""https://ps.w.org/paygate-payweb-for-woocommerce/assets/icon-128×128.png?rev=2999864"", ""2x"": ""https://ps.w.org/paygate-payweb-for-woocommerce/assets/icon-256x256.png?rev=2999864""}"
// admin-color-scheme	"{""1x"": ""https://ps.w.org/admin-color-scheme/assets/icon.svg?rev=2897183"", ""svg"": ""https://ps.w.org/admin-color-scheme/assets/icon.svg?rev=2897183""}"
// admin-collapse-subpages	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-collapse-subpages_e3e3e3.svg""}"
// admin-color-schemer	"{""1x"": ""https://ps.w.org/admin-color-schemer/assets/icon-128x128.png?rev=2391923"", ""2x"": ""https://ps.w.org/admin-color-schemer/assets/icon-256x256.png?rev=2391923""}"
// admin-color-schemes	"{""1x"": ""https://ps.w.org/admin-color-schemes/assets/icon.svg?rev=1016272"", ""svg"": ""https://ps.w.org/admin-color-schemes/assets/icon.svg?rev=1016272""}"
// admin-column-custom	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-column-custom.svg""}"
// admin-columns-for-acf-fields	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-columns-for-acf-fields.svg""}"
// admin-comment	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-comment.svg""}"
// admin-commenters-comments-count	"{""1x"": ""https://ps.w.org/admin-commenters-comments-count/assets/icon-128x128.png?rev=975860""}"
// admin-classic-borders	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-classic-borders_88afc6.svg""}"
// admin-country-allowlist	"{""1x"": ""https://ps.w.org/admin-country-allowlist/assets/icon.svg?rev=3030758"", ""svg"": ""https://ps.w.org/admin-country-allowlist/assets/icon.svg?rev=3030758""}"
// admin-color-bar	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-color-bar.svg""}"
// admin-colors-plus-visited	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-colors-plus-visited.svg""}"
// admin-custom-description	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-custom-description.svg""}"
// admin-custom-font	"{""1x"": ""https://ps.w.org/admin-custom-font/assets/icon-128x128.png?rev=1508363"", ""2x"": ""https://ps.w.org/admin-custom-font/assets/icon-256x256.png?rev=1508363""}"
// admin-custom-login	"{""1x"": ""https://ps.w.org/admin-custom-login/assets/icon-128x128.png?rev=1121656"", ""2x"": ""https://ps.w.org/admin-custom-login/assets/icon-256x256.png?rev=1121656""}"
// admin-column-view-selector	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-column-view-selector.svg""}"
// admin-dark-mode	"{""1x"": ""https://ps.w.org/admin-dark-mode/assets/icon-256x256.png?rev=1934289"", ""2x"": ""https://ps.w.org/admin-dark-mode/assets/icon-256x256.png?rev=1934289""}"
// admin-email-address-changer	"{""1x"": ""https://ps.w.org/admin-email-address-changer/assets/icon-256x256.png?rev=2536985"", ""2x"": ""https://ps.w.org/admin-email-address-changer/assets/icon-256x256.png?rev=2536985""}"
// advanced-css-editor	"{""1x"": ""https://ps.w.org/advanced-css-editor/assets/icon-128x128.png?rev=1367935""}"
// admin-columns-icons-addon	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-columns-icons-addon_95b2bd.svg""}"
// admin-customizer	"{""1x"": ""https://ps.w.org/admin-customizer/assets/icon-256x256.png?rev=2661257"", ""2x"": ""https://ps.w.org/admin-customizer/assets/icon-256x256.png?rev=2661257""}"
// admin-command-palette	"{""1x"": ""https://ps.w.org/admin-command-palette/assets/icon-256x256.jpg?rev=1227165"", ""2x"": ""https://ps.w.org/admin-command-palette/assets/icon-256x256.jpg?rev=1227165""}"
// admin-dashboard	"{""1x"": ""https://ps.w.org/admin-dashboard/assets/icon-128x128.png?rev=2147494"", ""2x"": ""https://ps.w.org/admin-dashboard/assets/icon-256x256.png?rev=2147494""}"
// admin-dashboard-last-edits	"{""1x"": ""https://ps.w.org/admin-dashboard-last-edits/assets/icon-128x128.png?rev=1088153"", ""2x"": ""https://ps.w.org/admin-dashboard-last-edits/assets/icon-256x256.png?rev=1088153""}"
// admin-dropdown-categories	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-dropdown-categories_fdfdfd.svg""}"
// admin-edit-comment	"{""1x"": ""https://ps.w.org/admin-edit-comment/assets/icon-128x128.png?rev=1998515"", ""2x"": ""https://ps.w.org/admin-edit-comment/assets/icon-256x256.png?rev=1998515""}"
// admin-email-as-from-address	"{""1x"": ""https://ps.w.org/admin-email-as-from-address/assets/icon.svg?rev=1490807"", ""svg"": ""https://ps.w.org/admin-email-as-from-address/assets/icon.svg?rev=1490807""}"
// admin-email-carbon-copy	"{""1x"": ""https://ps.w.org/admin-email-carbon-copy/assets/icon-128x128.png?rev=1849141"", ""2x"": ""https://ps.w.org/admin-email-carbon-copy/assets/icon-256x256.png?rev=1849141""}"
// admin-email-change	"{""1x"": ""https://ps.w.org/admin-email-change/assets/icon-128x128.png?rev=2872935""}"
// admin-events-extended	"{""1x"": ""https://ps.w.org/admin-events-extended/assets/icon.svg?rev=1694227"", ""svg"": ""https://ps.w.org/admin-events-extended/assets/icon.svg?rev=1694227""}"
// admin-expand-image-widgets	"{""1x"": ""https://ps.w.org/admin-expand-image-widgets/assets/icon-128x128.jpg?rev=2241121"", ""2x"": ""https://ps.w.org/admin-expand-image-widgets/assets/icon-256x256.jpg?rev=2241121""}"
// admin-featured-image	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-featured-image_7c90a1.svg""}"
// admin-featured-thumbnail	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-featured-thumbnail.svg""}"
// admin-expert-mode	"{""1x"": ""https://ps.w.org/admin-expert-mode/assets/icon-128x128.png?rev=1092282""}"
// admin-filter-posts-by-year	"{""1x"": ""https://ps.w.org/admin-filter-posts-by-year/assets/icon-128x128.png?rev=1362100"", ""2x"": ""https://ps.w.org/admin-filter-posts-by-year/assets/icon-256x256.png?rev=1362100""}"
// admin-footer-version-rebranded	"{""1x"": ""https://ps.w.org/admin-footer-version-rebranded/assets/icon-128x128.jpg?rev=1792400"", ""2x"": ""https://ps.w.org/admin-footer-version-rebranded/assets/icon-256x256.jpg?rev=1792401""}"
// admin-form-framework	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-form-framework_df5858.svg""}"
// admin-form	"{""1x"": ""https://ps.w.org/admin-form/assets/icon.svg?rev=2816851"", ""svg"": ""https://ps.w.org/admin-form/assets/icon.svg?rev=2816851""}"
// admin-global-search	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-global-search.svg""}"
// create-with-code	"{""1x"": ""https://ps.w.org/create-with-code/assets/icon-128x128.png?rev=1727049"", ""2x"": ""https://ps.w.org/create-with-code/assets/icon-256x256.png?rev=1727049""}"
// admin-goto	"{""1x"": ""https://ps.w.org/admin-goto/assets/icon-128x128.png?rev=1879713""}"
// agile-crm-landing-pages	"{""1x"": ""https://ps.w.org/agile-crm-landing-pages/assets/icon-128x128.png?rev=1793522"", ""2x"": ""https://ps.w.org/agile-crm-landing-pages/assets/icon-256x256.png?rev=1793522""}"
// admin-hangul-font	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-hangul-font.svg""}"
// admin-header-note	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-header-note.svg""}"
// admin-links-plus-alp-widget	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-links-plus-alp-widget.svg""}"
// admin-help-docs	"{""1x"": ""https://ps.w.org/admin-help-docs/assets/icon-128x128.png?rev=2853139"", ""2x"": ""https://ps.w.org/admin-help-docs/assets/icon-256x256.png?rev=2853139""}"
// admin-hide-tag-filter	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-hide-tag-filter.svg""}"
// admin-hot-maintenance-mode	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-hot-maintenance-mode.svg""}"
// admin-icons-manager	"{""1x"": ""https://ps.w.org/admin-icons-manager/assets/icon-128x128.png?rev=2255207"", ""2x"": ""https://ps.w.org/admin-icons-manager/assets/icon-256x256.png?rev=2255207""}"
// admin-instant-search	"{""1x"": ""https://ps.w.org/admin-instant-search/assets/icon-128x128.png?rev=3184802"", ""2x"": ""https://ps.w.org/admin-instant-search/assets/icon-256x256.png?rev=3184802""}"
// admin-ide-dark-mode	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-ide-dark-mode.svg""}"
// admin-in-english	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-in-english.svg""}"
// admin-in-english-with-switch	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-in-english-with-switch.svg""}"
// admin-in-menu	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-in-menu.svg""}"
// admin-ip-watcher	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-ip-watcher.svg""}"
// admin-keys	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-keys.svg""}"
// admin-language	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-language.svg""}"
// advanced-css3-related-posts-widget	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-css3-related-posts-widget_fbfbfb.svg""}"
// advanced-custom-css	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-css.svg""}"
// admin-link-box	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-link-box.svg""}"
// admin-live-search	"{""1x"": ""https://ps.w.org/admin-live-search/assets/icon-128x128.png?rev=1952576"", ""2x"": ""https://ps.w.org/admin-live-search/assets/icon-256x256.png?rev=1952576""}"
// admin-links-sidebar-widget	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-links-sidebar-widget.svg""}"
// admin-loaded-display	"{""1x"": ""https://ps.w.org/admin-loaded-display/assets/icon-128x128.png?rev=2095932""}"
// admin-locale	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-locale.svg""}"
// admin-login-custom-form	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-login-custom-form_c5b2a6.svg""}"
// admin-login-monitor	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-login-monitor.svg""}"
// advanced-csv-importer	"{""1x"": ""https://ps.w.org/advanced-csv-importer/assets/icon-128x128.png?rev=1056456""}"
// advanced-currency-switcher	"{""1x"": ""https://ps.w.org/advanced-currency-switcher/assets/icon.svg?rev=2892734"", ""svg"": ""https://ps.w.org/advanced-currency-switcher/assets/icon.svg?rev=2892734""}"
// admin-login-notifier	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-login-notifier.svg""}"
// admin-login-sms-notification	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-login-sms-notification.svg""}"
// admin-login-template	"{""1x"": ""https://ps.w.org/admin-login-template/assets/icon-128x128.png?rev=2685739"", ""2x"": ""https://ps.w.org/admin-login-template/assets/icon-256x256.png?rev=2685739""}"
// builderall-cheetah-for-wp	"{""1x"": ""https://ps.w.org/builderall-cheetah-for-wp/assets/icon-128x128.png?rev=2828887""}"
// lupsonline-link-netwerk	"{""1x"": ""https://ps.w.org/lupsonline-link-netwerk/assets/icon-128x128.png?rev=3031634""}"
// admin-menu-customizer	"{""1x"": ""https://ps.w.org/admin-menu-customizer/assets/icon-128x128.png?rev=2829709"", ""2x"": ""https://ps.w.org/admin-menu-customizer/assets/icon-256x256.png?rev=2829709""}"
// a-ads	"{""1x"": ""https://ps.w.org/a-ads/assets/icon.svg?rev=3131518"", ""svg"": ""https://ps.w.org/a-ads/assets/icon.svg?rev=3131518""}"
// admin-management-xtended	"{""1x"": ""https://ps.w.org/admin-management-xtended/assets/icon-128x128.png?rev=1163110"", ""2x"": ""https://ps.w.org/admin-management-xtended/assets/icon-256x256.png?rev=1162226""}"
// admin-menu-class-by-010pixel	"{""1x"": ""https://ps.w.org/admin-menu-class-by-010pixel/assets/icon.svg?rev=1093129"", ""svg"": ""https://ps.w.org/admin-menu-class-by-010pixel/assets/icon.svg?rev=1093129""}"
// admin-menu-creator	"{""1x"": ""https://ps.w.org/admin-menu-creator/assets/icon-128x128.jpg?rev=1903145""}"
// admin-menu-filter	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-menu-filter.svg""}"
// admin-menu-groups	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-menu-groups.svg""}"
// admin-menu-in-frontend	"{""1x"": ""https://ps.w.org/admin-menu-in-frontend/assets/icon-128x128.png?rev=1746084"", ""2x"": ""https://ps.w.org/admin-menu-in-frontend/assets/icon-256x256.png?rev=1746084""}"
// admin-menu-manager	"{""1x"": ""https://ps.w.org/admin-menu-manager/assets/icon.svg?rev=1146706"", ""svg"": ""https://ps.w.org/admin-menu-manager/assets/icon.svg?rev=1146706""}"
// a-better-prezi-wordprezi	"{""default"": ""https://s.w.org/plugins/geopattern-icon/a-better-prezi-wordprezi.svg""}"
// a-broad-hint	"{""default"": ""https://s.w.org/plugins/geopattern-icon/a-broad-hint_ffffff.svg""}"
// advanced-custom-blocks	"{""1x"": ""https://ps.w.org/advanced-custom-blocks/assets/icon.svg?rev=1919623"", ""svg"": ""https://ps.w.org/advanced-custom-blocks/assets/icon.svg?rev=1919623""}"
// a-click-tracker	"{""default"": ""https://s.w.org/plugins/geopattern-icon/a-click-tracker.svg""}"
// advanced-custom-data	"{""1x"": ""https://ps.w.org/advanced-custom-data/assets/icon-128x128.png?rev=3006522""}"
// advanced-custom-field-repeater-collapser	"{""1x"": ""https://ps.w.org/advanced-custom-field-repeater-collapser/assets/icon.svg?rev=974133"", ""svg"": ""https://ps.w.org/advanced-custom-field-repeater-collapser/assets/icon.svg?rev=974133""}"
// advanced-custom-fields-address-field-add-on	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-address-field-add-on.svg""}"
// agile-crm-lead-management	"{""1x"": ""https://ps.w.org/agile-crm-lead-management/assets/icon-128x128.png?rev=1556116"", ""2x"": ""https://ps.w.org/agile-crm-lead-management/assets/icon-256x256.png?rev=1556116""}"
// admin-menu-on-right	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-menu-on-right.svg""}"
// admin-menu-width	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-menu-width.svg""}"
// admin-menu-post-list	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-menu-post-list_acacac.svg""}"
// admin-menu-remover	"{""1x"": ""https://ps.w.org/admin-menu-remover/assets/icon-128x128.png?rev=1268502"", ""2x"": ""https://ps.w.org/admin-menu-remover/assets/icon-256x256.png?rev=1268498""}"
// admin-menu-restrictor	"{""1x"": ""https://ps.w.org/admin-menu-restrictor/assets/icon-256x256.jpg?rev=3197686"", ""2x"": ""https://ps.w.org/admin-menu-restrictor/assets/icon-256x256.jpg?rev=3197686""}"
// admin-menu-slide	"{""1x"": ""https://ps.w.org/admin-menu-slide/assets/icon-128x128.png?rev=1209713"", ""2x"": ""https://ps.w.org/admin-menu-slide/assets/icon-256x256.png?rev=1209713""}"
// optin	"{""1x"": ""https://ps.w.org/optin/assets/icon-128X128.png?rev=3212524"", ""2x"": ""https://ps.w.org/optin/assets/icon-256x256.png?rev=3212524""}"
// admin-menu-search	"{""1x"": ""https://ps.w.org/admin-menu-search/assets/icon-128x128.jpg?rev=2141492""}"
// admin-menu-tamplate-plugin	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-menu-tamplate-plugin.svg""}"
// admin-menu-search-ams	"{""1x"": ""https://ps.w.org/admin-menu-search-ams/assets/icon-256x256.png?rev=3178725"", ""2x"": ""https://ps.w.org/admin-menu-search-ams/assets/icon-256x256.png?rev=3178725""}"
// admin-menu-tree-page-view	"{""1x"": ""https://ps.w.org/admin-menu-tree-page-view/assets/icon-256x256.png?rev=2984432"", ""2x"": ""https://ps.w.org/admin-menu-tree-page-view/assets/icon-256x256.png?rev=2984432""}"
// html-to-pdf-converter	"{""1x"": ""https://ps.w.org/html-to-pdf-converter/assets/icon-128x128.png?rev=3157463""}"
// admin-menus-accessibility	"{""1x"": ""https://ps.w.org/admin-menus-accessibility/assets/icon-256x256.png?rev=1487762"", ""2x"": ""https://ps.w.org/admin-menus-accessibility/assets/icon-256x256.png?rev=1487762""}"
// admin-menu-slugs	"{""1x"": ""https://ps.w.org/admin-menu-slugs/assets/icon-256x256.png?rev=2651269"", ""2x"": ""https://ps.w.org/admin-menu-slugs/assets/icon-256x256.png?rev=2651269""}"
// admin-menus-fixed	"{""1x"": ""https://ps.w.org/admin-menus-fixed/assets/icon-128x128.jpg?rev=1015837""}"
// admin-meta-search	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-meta-search_f3f8fa.svg""}"
// agile-crm-newsletter	"{""1x"": ""https://ps.w.org/agile-crm-newsletter/assets/icon-128x128.png?rev=1793977"", ""2x"": ""https://ps.w.org/agile-crm-newsletter/assets/icon-256x256.png?rev=1793977""}"
// admin-msg-board	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-msg-board.svg""}"
// admin-note	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-note_fdfdfd.svg""}"
// admin-notebook	"{""1x"": ""https://ps.w.org/admin-notebook/assets/icon-128x128.jpg?rev=1851227"", ""2x"": ""https://ps.w.org/admin-notebook/assets/icon-256x256.jpg?rev=1851227""}"
// acf-starrating	"{""default"": ""https://s.w.org/plugins/geopattern-icon/acf-starrating.svg""}"
// admin-notes	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-notes.svg""}"
// admin-notes-wp	"{""1x"": ""https://ps.w.org/admin-notes-wp/assets/icon-128x128.png?rev=2499100"", ""2x"": ""https://ps.w.org/admin-notes-wp/assets/icon-256x256.png?rev=2499100""}"
// admin-notice	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-notice.svg""}"
// admin-notices-for-woocommerce	"{""1x"": ""https://ps.w.org/admin-notices-for-woocommerce/assets/icon-128x128.png?rev=2164484"", ""2x"": ""https://ps.w.org/admin-notices-for-woocommerce/assets/icon-256x256.png?rev=2164484""}"
// admin-only-dashboard	"{""1x"": ""https://ps.w.org/admin-only-dashboard/assets/icon-256x256.png?rev=3074610"", ""2x"": ""https://ps.w.org/admin-only-dashboard/assets/icon-256x256.png?rev=3074610""}"
// admin-only-jetpack	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-only-jetpack.svg""}"
// admin-options-pages	"{""1x"": ""https://ps.w.org/admin-options-pages/assets/icon.svg?rev=2058406"", ""svg"": ""https://ps.w.org/admin-options-pages/assets/icon.svg?rev=2058406""}"
// admin-page-framework	"{""1x"": ""https://ps.w.org/admin-page-framework/assets/icon-128x128.png?rev=998199"", ""2x"": ""https://ps.w.org/admin-page-framework/assets/icon-256x256.png?rev=998199""}"
// admin-page-notes	"{""1x"": ""https://ps.w.org/admin-page-notes/assets/icon-128x128.png?rev=2902021"", ""2x"": ""https://ps.w.org/admin-page-notes/assets/icon-256x256.png?rev=2902021""}"
// admin-page-spider	"{""1x"": ""https://ps.w.org/admin-page-spider/assets/icon-128x128.png?rev=1411010""}"
// admin-panel-background-color	"{""1x"": ""https://ps.w.org/admin-panel-background-color/assets/icon-128x128.png?rev=2133621"", ""2x"": ""https://ps.w.org/admin-panel-background-color/assets/icon-256x256.png?rev=2133621""}"
// admin-per-page-limits	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-per-page-limits.svg""}"
// admin-php-eval	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-php-eval.svg""}"
// advanced-footnotes	"{""1x"": ""https://ps.w.org/advanced-footnotes/assets/icon-128x128.jpg?rev=1885926"", ""2x"": ""https://ps.w.org/advanced-footnotes/assets/icon-256x256.jpg?rev=1885926""}"
// advanced-free-flat-shipping-woocommerce	"{""1x"": ""https://ps.w.org/advanced-free-flat-shipping-woocommerce/assets/icon.svg?rev=2700613"", ""svg"": ""https://ps.w.org/advanced-free-flat-shipping-woocommerce/assets/icon.svg?rev=2700613""}"
// admin-post-formats-filter	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-post-formats-filter.svg""}"
// admin-plugins-description	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-plugins-description.svg""}"
// admin-post-info	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-post-info.svg""}"
// advanced-custom-field-widget	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-field-widget.svg""}"
// advanced-fuzzy-product-search-for-woocommerce	"{""1x"": ""https://ps.w.org/advanced-fuzzy-product-search-for-woocommerce/assets/icon-128x128.png?rev=3186664"", ""2x"": ""https://ps.w.org/advanced-fuzzy-product-search-for-woocommerce/assets/icon-256x256.png?rev=3186664""}"
// customeasy	"{""1x"": ""https://ps.w.org/customeasy/assets/icon.svg?rev=2334185"", ""svg"": ""https://ps.w.org/customeasy/assets/icon.svg?rev=2334185""}"
// admin-post-notifier	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-post-notifier.svg""}"
// admin-post-reminder	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-post-reminder.svg""}"
// admin-post-tag-filter	"{""1x"": ""https://ps.w.org/admin-post-tag-filter/assets/icon-128x128.png?rev=1445418""}"
// admin-posts-grid	"{""1x"": ""https://ps.w.org/admin-posts-grid/assets/icon-128x128.png?rev=2744599"", ""2x"": ""https://ps.w.org/admin-posts-grid/assets/icon-256x256.png?rev=2744599""}"
// admin-posts-list-tag-filter	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-posts-list-tag-filter_f0f0f0.svg""}"
// admin-posts-manager	"{""1x"": ""https://ps.w.org/admin-posts-manager/assets/icon-128x128.png?rev=2746138"", ""2x"": ""https://ps.w.org/admin-posts-manager/assets/icon-256x256.png?rev=2746138""}"
// admin-toolbox	"{""1x"": ""https://ps.w.org/admin-toolbox/assets/icon-256x256.png?rev=1797827"", ""2x"": ""https://ps.w.org/admin-toolbox/assets/icon-256x256.png?rev=1797827""}"
// admin-previous-and-next-order-edit-links-for-woocommerce	"{""1x"": ""https://ps.w.org/admin-previous-and-next-order-edit-links-for-woocommerce/assets/icon-128x128.png?rev=2987334"", ""2x"": ""https://ps.w.org/admin-previous-and-next-order-edit-links-for-woocommerce/assets/icon-256x256.png?rev=2987334""}"
// admin-private-note-on-users	"{""1x"": ""https://ps.w.org/admin-private-note-on-users/assets/icon-128x128.png?rev=1636524"", ""2x"": ""https://ps.w.org/admin-private-note-on-users/assets/icon-256x256.png?rev=1636531""}"
// admin-pro	"{""1x"": ""https://ps.w.org/admin-pro/assets/icon-128x128.png?rev=1963105"", ""2x"": ""https://ps.w.org/admin-pro/assets/icon-256x256.png?rev=1963105""}"
// hd-quiz-save-results-light	"{""1x"": ""https://ps.w.org/hd-quiz-save-results-light/assets/icon-128x128.png?rev=2215392""}"
// admin-quick-jump	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-quick-jump_00539e.svg""}"
// admin-quick-panel	"{""1x"": ""https://ps.w.org/admin-quick-panel/assets/icon-128x128.png?rev=2128045"", ""2x"": ""https://ps.w.org/admin-quick-panel/assets/icon-256x256.png?rev=2128045""}"
// admin-quicksearch	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-quicksearch.svg""}"
// admin-restriction	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-restriction.svg""}"
// admin-tools	"{""1x"": ""https://ps.w.org/admin-tools/assets/icon-256x256.png?rev=1470496"", ""2x"": ""https://ps.w.org/admin-tools/assets/icon-256x256.png?rev=1470496""}"
// admin-right-click-menu	"{""1x"": ""https://ps.w.org/admin-right-click-menu/assets/icon-128x128.png?rev=1479922"", ""2x"": ""https://ps.w.org/admin-right-click-menu/assets/icon-256x256.png?rev=1479922""}"
// admin-screenshots	"{""1x"": ""https://ps.w.org/admin-screenshots/assets/icon-128x128.png?rev=2879788"", ""2x"": ""https://ps.w.org/admin-screenshots/assets/icon-256x256.png?rev=2879788""}"
// admin-search	"{""1x"": ""https://ps.w.org/admin-search/assets/icon.svg?rev=2263465"", ""svg"": ""https://ps.w.org/admin-search/assets/icon.svg?rev=2263465""}"
// advanced-forms	"{""1x"": ""https://ps.w.org/advanced-forms/assets/icon-128x128.png?rev=1894251"", ""2x"": ""https://ps.w.org/advanced-forms/assets/icon-256x256.png?rev=1894254""}"
// agile-crm-webrules	"{""1x"": ""https://ps.w.org/agile-crm-webrules/assets/icon-128x128.png?rev=1795603"", ""2x"": ""https://ps.w.org/agile-crm-webrules/assets/icon-256x256.png?rev=1795603""}"
// admin-select-box-to-select2	"{""1x"": ""https://ps.w.org/admin-select-box-to-select2/assets/icon.svg?rev=1720952"", ""svg"": ""https://ps.w.org/admin-select-box-to-select2/assets/icon.svg?rev=1720952""}"
// admin-setting	"{""1x"": ""https://ps.w.org/admin-setting/assets/icon-256x256.png?rev=2229791"", ""2x"": ""https://ps.w.org/admin-setting/assets/icon-256x256.png?rev=2229791""}"
// admin-shipping-calculator	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-shipping-calculator.svg""}"
// allow-swf-upload	"{""default"": ""https://s.w.org/plugins/geopattern-icon/allow-swf-upload_c49f9f.svg""}"
// admin-show-sticky	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-show-sticky.svg""}"
// admin-site-switcher	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-site-switcher.svg""}"
// admin-slug-column	"{""1x"": ""https://ps.w.org/admin-slug-column/assets/icon-128x128.png?rev=1963931"", ""2x"": ""https://ps.w.org/admin-slug-column/assets/icon-256x256.png?rev=1963931""}"
// admin-social-shares	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-social-shares_9499ca.svg""}"
// admin-spam-colour-changer	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-spam-colour-changer.svg""}"
// admin-speedo	"{""1x"": ""https://ps.w.org/admin-speedo/assets/icon-128x128.jpg?rev=2824612"", ""2x"": ""https://ps.w.org/admin-speedo/assets/icon-256x256.jpg?rev=2824612""}"
// admin-ssl-secure-admin	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-ssl-secure-admin.svg""}"
// admin-starred-posts	"{""1x"": ""https://ps.w.org/admin-starred-posts/assets/icon-128x128.png?rev=1669994""}"
// advanced-custom-fields-code-area-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-code-area-field_212121.svg""}"
// alixcan-alinti-yap	"{""default"": ""https://s.w.org/plugins/geopattern-icon/alixcan-alinti-yap.svg""}"
// advanced-custom-fields-contact-form-7-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-contact-form-7-field_cfe4f8.svg""}"
// ai-content-copilot-auto-social-media-posting	"{""1x"": ""https://ps.w.org/ai-content-copilot-auto-social-media-posting/assets/icon-128x128.png?rev=3090391"", ""2x"": ""https://ps.w.org/ai-content-copilot-auto-social-media-posting/assets/icon-256x256.png?rev=3090391""}"
// admin-sticky-notes	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-sticky-notes.svg""}"
// admin-sticky-sidebar	"{""1x"": ""https://ps.w.org/admin-sticky-sidebar/assets/icon-128-128.png?rev=2654278"", ""2x"": ""https://ps.w.org/admin-sticky-sidebar/assets/icon-256-256.png?rev=2654278""}"
// admin-sticky-widget-areas	"{""1x"": ""https://ps.w.org/admin-sticky-widget-areas/assets/icon-128x128.png?rev=1784436"", ""2x"": ""https://ps.w.org/admin-sticky-widget-areas/assets/icon-256x256.png?rev=1784436""}"
// admin-tag-ui	"{""1x"": ""https://ps.w.org/admin-tag-ui/assets/icon-128x128.png?rev=1665706"", ""2x"": ""https://ps.w.org/admin-tag-ui/assets/icon-256x256.png?rev=1665706""}"
// admin-tailor	"{""1x"": ""https://ps.w.org/admin-tailor/assets/icon-128x128.png?rev=2787925"", ""2x"": ""https://ps.w.org/admin-tailor/assets/icon-256x256.png?rev=2787925""}"
// admin-taxonomy-autocomplete	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-taxonomy-autocomplete.svg""}"
// quick-id-viewer	"{""default"": ""https://s.w.org/plugins/geopattern-icon/quick-id-viewer.svg""}"
// admin-temasi	"{""1x"": ""https://ps.w.org/admin-temasi/assets/icon-128x128.png?rev=1707804"", ""2x"": ""https://ps.w.org/admin-temasi/assets/icon-256x256.png?rev=1707804""}"
// admin-taxonomy-filter	"{""1x"": ""https://ps.w.org/admin-taxonomy-filter/assets/icon-128x128.png?rev=1699286""}"
// ai-content-generate	"{""1x"": ""https://ps.w.org/ai-content-generate/assets/icon-128x128.png?rev=3179892"", ""2x"": ""https://ps.w.org/ai-content-generate/assets/icon-256x256.png?rev=3179892""}"
// admin-temp-directory	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-temp-directory.svg""}"
// admin-thumbnails	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-thumbnails.svg""}"
// admin-title-check	"{""1x"": ""https://ps.w.org/admin-title-check/assets/icon-128x128.png?rev=1685861"", ""2x"": ""https://ps.w.org/admin-title-check/assets/icon-256x256.png?rev=1685861""}"
// admin-todotastic	"{""1x"": ""https://ps.w.org/admin-todotastic/assets/icon-128x128.png?rev=2593157"", ""2x"": ""https://ps.w.org/admin-todotastic/assets/icon-256x256.png?rev=2593157""}"
// admin-toolbar-menus	"{""1x"": ""https://ps.w.org/admin-toolbar-menus/assets/icon.svg?rev=981026"", ""svg"": ""https://ps.w.org/admin-toolbar-menus/assets/icon.svg?rev=981026""}"
// admin-toolchain	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-toolchain.svg""}"
// admin-top-menu	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-top-menu_a4a6a8.svg""}"
// advanced-custom-fields-font-awesome	"{""1x"": ""https://ps.w.org/advanced-custom-fields-font-awesome/assets/icon-128x128.jpg?rev=1016227"", ""2x"": ""https://ps.w.org/advanced-custom-fields-font-awesome/assets/icon-256x256.jpg?rev=1016227""}"
// admin-topbar-visibility	"{""1x"": ""https://ps.w.org/admin-topbar-visibility/assets/icon-128x128.jpg?rev=2570100"", ""2x"": ""https://ps.w.org/admin-topbar-visibility/assets/icon-256x256.jpg?rev=2570100""}"
// admin-tour	"{""1x"": ""https://ps.w.org/admin-tour/assets/icon-128x128.png?rev=2604259"", ""2x"": ""https://ps.w.org/admin-tour/assets/icon-256x256.png?rev=2604259""}"
// admin-ui	"{""1x"": ""https://ps.w.org/admin-ui/assets/icon-256x256.png?rev=2429859"", ""2x"": ""https://ps.w.org/admin-ui/assets/icon-256x256.png?rev=2429859""}"
// admin-ui-simplificator	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-ui-simplificator.svg""}"
// admin-user-control	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-user-control.svg""}"
// admin-user-delete-with-contents-disabled	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-user-delete-with-contents-disabled.svg""}"
// admin-user-messages	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-user-messages_f7f8f9.svg""}"
// admin-user-message	"{""1x"": ""https://ps.w.org/admin-user-message/assets/icon.svg?rev=1016687"", ""svg"": ""https://ps.w.org/admin-user-message/assets/icon.svg?rev=1016687""}"
// admin-user-search	"{""1x"": ""https://ps.w.org/admin-user-search/assets/icon-256x256.png?rev=2752212"", ""2x"": ""https://ps.w.org/admin-user-search/assets/icon-256x256.png?rev=2752212""}"
// admin-username-changer	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-username-changer.svg""}"
// admin-users-advances-permissions	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-users-advances-permissions.svg""}"
// admin-users-logged-in	"{""1x"": ""https://ps.w.org/admin-users-logged-in/assets/icon-128x128.png?rev=1818906"", ""2x"": ""https://ps.w.org/admin-users-logged-in/assets/icon-256x256.png?rev=1818906""}"
// ai-keyword-swap	"{""1x"": ""https://ps.w.org/ai-keyword-swap/assets/icon-128x128.png?rev=3185726""}"
// admin-zendesk-help-widget	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin-zendesk-help-widget.svg""}"
// admin10x	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admin10x.svg""}"
// adminbar-no-customizer	"{""1x"": ""https://ps.w.org/adminbar-no-customizer/assets/icon-128x128.png?rev=1311368"", ""2x"": ""https://ps.w.org/adminbar-no-customizer/assets/icon-256x256.png?rev=1311368""}"
// adminbar-drag-hide	"{""1x"": ""https://ps.w.org/adminbar-drag-hide/assets/icon-128x128.jpg?rev=2237864"", ""2x"": ""https://ps.w.org/adminbar-drag-hide/assets/icon-256x256.jpg?rev=2237864""}"
// cardealer	"{""1x"": ""https://ps.w.org/cardealer/assets/icon-128x128.gif?rev=2525924"", ""2x"": ""https://ps.w.org/cardealer/assets/icon-256x256.gif?rev=2525924""}"
// adminbar-link-comments-to-pending	"{""1x"": ""https://ps.w.org/adminbar-link-comments-to-pending/assets/icon-128x128.png?rev=981185"", ""2x"": ""https://ps.w.org/adminbar-link-comments-to-pending/assets/icon-256x256.png?rev=981185""}"
// adminbar-manager	"{""1x"": ""https://ps.w.org/adminbar-manager/assets/icon-128x128.png?rev=1738156"", ""2x"": ""https://ps.w.org/adminbar-manager/assets/icon-256x256.png?rev=1738156""}"
// woo-xendit-virtual-accounts	"{""1x"": ""https://ps.w.org/woo-xendit-virtual-accounts/assets/icon-128x128.png?rev=1923672"", ""2x"": ""https://ps.w.org/woo-xendit-virtual-accounts/assets/icon-256x256.png?rev=1923672""}"
// adminbar-on-off	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adminbar-on-off.svg""}"
// advanced-custom-fields-location-field-add-on	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-location-field-add-on_ebe8d4.svg""}"
// advanced-custom-fields-google-map-extended	"{""1x"": ""https://ps.w.org/advanced-custom-fields-google-map-extended/assets/icon-128x128.png?rev=1285012"", ""2x"": ""https://ps.w.org/advanced-custom-fields-google-map-extended/assets/icon-256x256.png?rev=1285012""}"
// adminbar-remover	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adminbar-remover.svg""}"
// adminify	"{""1x"": ""https://ps.w.org/adminify/assets/icon.svg?rev=3001787"", ""svg"": ""https://ps.w.org/adminify/assets/icon.svg?rev=3001787""}"
// adminimal-bar	"{""1x"": ""https://ps.w.org/adminimal-bar/assets/icon-256x256.png?rev=2920926"", ""2x"": ""https://ps.w.org/adminimal-bar/assets/icon-256x256.png?rev=2920926""}"
// administracion-de-pedidos-servientrega	"{""1x"": ""https://ps.w.org/administracion-de-pedidos-servientrega/assets/icon-128x128.png?rev=2758218"", ""2x"": ""https://ps.w.org/administracion-de-pedidos-servientrega/assets/icon-256x256.png?rev=2758218""}"
// adminimal	"{""1x"": ""https://ps.w.org/adminimal/assets/icon-128x128.jpg?rev=2047212"", ""2x"": ""https://ps.w.org/adminimal/assets/icon-256x256.jpg?rev=2047212""}"
// adminimize	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adminimize_000000.svg""}"
// administrate-more-comments	"{""default"": ""https://s.w.org/plugins/geopattern-icon/administrate-more-comments_f3f3f3.svg""}"
// administrative-shortcodes	"{""default"": ""https://s.w.org/plugins/geopattern-icon/administrative-shortcodes.svg""}"
// administrator-access-to-pmpro-protected-content	"{""1x"": ""https://ps.w.org/administrator-access-to-pmpro-protected-content/assets/icon-128x128.jpg?rev=1947066"", ""2x"": ""https://ps.w.org/administrator-access-to-pmpro-protected-content/assets/icon-256x256.jpg?rev=1947066""}"
// administrator-only	"{""default"": ""https://s.w.org/plugins/geopattern-icon/administrator-only.svg""}"
// advanced-custom-fields-leaflet-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-leaflet-field_dfd8ce.svg""}"
// adminpad	"{""1x"": ""https://ps.w.org/adminpad/assets/icon-256x256.png?rev=1411431"", ""2x"": ""https://ps.w.org/adminpad/assets/icon-256x256.png?rev=1411431""}"
// adminpage-helper	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adminpage-helper.svg""}"
// adminpass-password-bypass-display	"{""1x"": ""https://ps.w.org/adminpass-password-bypass-display/assets/icon-128x128.png?rev=3135039"", ""2x"": ""https://ps.w.org/adminpass-password-bypass-display/assets/icon-256x256.png?rev=3135039""}"
// adminpress	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adminpress_6d5c62.svg""}"
// admins-post-statistics	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admins-post-statistics.svg""}"
// adminstrip	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adminstrip.svg""}"
// admins-debug-tool	"{""1x"": ""https://ps.w.org/admins-debug-tool/assets/icon-128x128.png?rev=1223216"", ""2x"": ""https://ps.w.org/admins-debug-tool/assets/icon-256x256.png?rev=1223216""}"
// adminsanity	"{""1x"": ""https://ps.w.org/adminsanity/assets/icon-128x128.png?rev=2495609"", ""2x"": ""https://ps.w.org/adminsanity/assets/icon-256x256.png?rev=2495609""}"
// adminquickbar	"{""1x"": ""https://ps.w.org/adminquickbar/assets/icon-256x256.jpg?rev=1786824"", ""2x"": ""https://ps.w.org/adminquickbar/assets/icon-256x256.jpg?rev=1786824""}"
// admiral-adblock-suite	"{""1x"": ""https://ps.w.org/admiral-adblock-suite/assets/icon-128x128.png?rev=1430494"", ""2x"": ""https://ps.w.org/admiral-adblock-suite/assets/icon-256x256.png?rev=1430494""}"
// admire-extra	"{""1x"": ""https://ps.w.org/admire-extra/assets/icon-128x128.png?rev=2867984""}"
// admitad-tracking	"{""1x"": ""https://ps.w.org/admitad-tracking/assets/icon-128x128.png?rev=1744620""}"
// admium	"{""default"": ""https://s.w.org/plugins/geopattern-icon/admium.svg""}"
// adngin-your-adsense-your-traffic-maximized-revenue-for-free	"{""1x"": ""https://ps.w.org/adngin-your-adsense-your-traffic-maximized-revenue-for-free/assets/icon-128x128.png?rev=1194707""}"
// adobe-analytics	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adobe-analytics_f82f41.svg""}"
// adnkronos-feed-importer	"{""1x"": ""https://ps.w.org/adnkronos-feed-importer/assets/icon-128x128.png?rev=2909284"", ""2x"": ""https://ps.w.org/adnkronos-feed-importer/assets/icon-256x256.png?rev=2909284""}"
// adobe-dtm	"{""1x"": ""https://ps.w.org/adobe-dtm/assets/icon-128x128.png?rev=1504124"", ""2x"": ""https://ps.w.org/adobe-dtm/assets/icon-256x256.png?rev=1504124""}"
// adobe-xmp-for-wp	"{""1x"": ""https://ps.w.org/adobe-xmp-for-wp/assets/icon-128x128.jpg?rev=2396815"", ""2x"": ""https://ps.w.org/adobe-xmp-for-wp/assets/icon-256x256.jpg?rev=2396815""}"
// accessibility-help-button	"{""1x"": ""https://ps.w.org/accessibility-help-button/assets/icon-128x128.png?rev=2027016""}"
// adonide-faq-plugin	"{""1x"": ""https://ps.w.org/adonide-faq-plugin/assets/icon-128x128.png?rev=1349864""}"
// adop-amp	"{""1x"": ""https://ps.w.org/adop-amp/assets/icon-128x128.png?rev=2062692""}"
// adoption	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adoption_564e4a.svg""}"
// adorable-avatars	"{""1x"": ""https://ps.w.org/adorable-avatars/assets/icon-128x128.png?rev=1342003"", ""2x"": ""https://ps.w.org/adorable-avatars/assets/icon-256x256.png?rev=1342003""}"
// carnet-de-vols	"{""1x"": ""https://ps.w.org/carnet-de-vols/assets/icon-128x128.png?rev=3085256""}"
// accessibility-language	"{""default"": ""https://s.w.org/plugins/geopattern-icon/accessibility-language.svg""}"
// accessibility-light	"{""1x"": ""https://ps.w.org/accessibility-light/assets/icon-128x128.jpg?rev=2123607"", ""2x"": ""https://ps.w.org/accessibility-light/assets/icon-256x256.jpg?rev=2123607""}"
// accessibility-menu-pro	"{""1x"": ""https://ps.w.org/accessibility-menu-pro/assets/icon-256x256.png?rev=3130403"", ""2x"": ""https://ps.w.org/accessibility-menu-pro/assets/icon-256x256.png?rev=3130403""}"
// accessibility-new-window-warnings	"{""1x"": ""https://ps.w.org/accessibility-new-window-warnings/assets/icon-128x128.png?rev=2944432"", ""2x"": ""https://ps.w.org/accessibility-new-window-warnings/assets/icon-256x256.png?rev=2944432""}"
// gamipress	"{""1x"": ""https://ps.w.org/gamipress/assets/icon-128x128.png?rev=1699714"", ""2x"": ""https://ps.w.org/gamipress/assets/icon-256x256.png?rev=1699714""}"
// accessibility-onetap	"{""1x"": ""https://ps.w.org/accessibility-onetap/assets/icon-128x128.gif?rev=3186519"", ""2x"": ""https://ps.w.org/accessibility-onetap/assets/icon-256x256.gif?rev=3186519""}"
// accessibility-plus	"{""1x"": ""https://ps.w.org/accessibility-plus/assets/icon-128x128.png?rev=2848886"", ""2x"": ""https://ps.w.org/accessibility-plus/assets/icon-256x256.png?rev=2848886""}"
// accessibility-spring	"{""1x"": ""https://ps.w.org/accessibility-spring/assets/icon-128x128.png?rev=1560845"", ""2x"": ""https://ps.w.org/accessibility-spring/assets/icon-256x256.png?rev=1560845""}"
// accessibility-statement	"{""1x"": ""https://ps.w.org/accessibility-statement/assets/icon.svg?rev=2684047"", ""svg"": ""https://ps.w.org/accessibility-statement/assets/icon.svg?rev=2684047""}"
// accessibility-toolbar	"{""1x"": ""https://ps.w.org/accessibility-toolbar/assets/icon-128x128.png?rev=2087233""}"
// accessibility-uuu-widget	"{""1x"": ""https://ps.w.org/accessibility-uuu-widget/assets/icon-256x256.png?rev=3136377"", ""2x"": ""https://ps.w.org/accessibility-uuu-widget/assets/icon-256x256.png?rev=3136377""}"
// accessibility-widget	"{""1x"": ""https://ps.w.org/accessibility-widget/assets/icon-128x128.png?rev=1315523"", ""2x"": ""https://ps.w.org/accessibility-widget/assets/icon-256x256.png?rev=1315523""}"
// accessibility-widget-by-adally	"{""1x"": ""https://ps.w.org/accessibility-widget-by-adally/assets/icon-256x256.png?rev=2375570"", ""2x"": ""https://ps.w.org/accessibility-widget-by-adally/assets/icon-256x256.png?rev=2375570""}"
// accessible-dropdown-menus	"{""default"": ""https://s.w.org/plugins/geopattern-icon/accessible-dropdown-menus.svg""}"
// accessible-elementor-popups-by-accessibility-zone	"{""1x"": ""https://ps.w.org/accessible-elementor-popups-by-accessibility-zone/assets/icon-128x128.png?rev=2738766"", ""2x"": ""https://ps.w.org/accessible-elementor-popups-by-accessibility-zone/assets/icon-256x256.png?rev=2738766""}"
// accessible-external-text-links	"{""1x"": ""https://ps.w.org/accessible-external-text-links/assets/icon-128x128.png?rev=2867607"", ""2x"": ""https://ps.w.org/accessible-external-text-links/assets/icon-256x256.png?rev=2867607""}"
// accessible-poetry	"{""1x"": ""https://ps.w.org/accessible-poetry/assets/icon.svg?rev=2785029"", ""svg"": ""https://ps.w.org/accessible-poetry/assets/icon.svg?rev=2785029""}"
// accessible-reading	"{""1x"": ""https://ps.w.org/accessible-reading/assets/icon-128x128.png?rev=2735973"", ""2x"": ""https://ps.w.org/accessible-reading/assets/icon-256x256.png?rev=2735973""}"
// allow-webp-file-upload	"{""1x"": ""https://ps.w.org/allow-webp-file-upload/assets/icon-128x128.png?rev=2699743"", ""2x"": ""https://ps.w.org/allow-webp-file-upload/assets/icon-256x256.png?rev=2699743""}"
// accessible-tag-cloud	"{""default"": ""https://s.w.org/plugins/geopattern-icon/accessible-tag-cloud.svg""}"
// accessible-video-library	"{""1x"": ""https://ps.w.org/accessible-video-library/assets/icon-128x128.png?rev=1097590"", ""2x"": ""https://ps.w.org/accessible-video-library/assets/icon-256x256.png?rev=1097590""}"
// aco-woo-dynamic-pricing	"{""1x"": ""https://ps.w.org/aco-woo-dynamic-pricing/assets/icon-128x128.png?rev=2822023"", ""2x"": ""https://ps.w.org/aco-woo-dynamic-pricing/assets/icon-256x256.png?rev=2822023""}"
// adplus	"{""1x"": ""https://ps.w.org/adplus/assets/icon-128x128.png?rev=1605883"", ""2x"": ""https://ps.w.org/adplus/assets/icon-256x256.png?rev=1605883""}"
// adplugg	"{""1x"": ""https://ps.w.org/adplugg/assets/icon-128x128.png?rev=1028280"", ""2x"": ""https://ps.w.org/adplugg/assets/icon-256x256.png?rev=1028280""}"
// adpop-for-wordpress	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adpop-for-wordpress.svg""}"
// adpushup	"{""1x"": ""https://ps.w.org/adpushup/assets/icon-128x128.png?rev=1157414"", ""2x"": ""https://ps.w.org/adpushup/assets/icon-256x256.png?rev=1157414""}"
// adrecord-affiliate	"{""1x"": ""https://ps.w.org/adrecord-affiliate/assets/icon-128x128.jpg?rev=2025120"", ""2x"": ""https://ps.w.org/adrecord-affiliate/assets/icon-256x256.jpg?rev=2025120""}"
// advanced-custom-fields-mapbox-geojson-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-mapbox-geojson-field_a6c4c1.svg""}"
// adredux	"{""1x"": ""https://ps.w.org/adredux/assets/icon-128x128.png?rev=1751693"", ""2x"": ""https://ps.w.org/adredux/assets/icon-256x256.png?rev=1751693""}"
// adresskorrektur-autocomplete-fuer-woo	"{""1x"": ""https://ps.w.org/adresskorrektur-autocomplete-fuer-woo/assets/icon-128x128.jpg?rev=2404371"", ""2x"": ""https://ps.w.org/adresskorrektur-autocomplete-fuer-woo/assets/icon-256x256.jpg?rev=2404371""}"
// adrotate-extra-settings	"{""1x"": ""https://ps.w.org/adrotate-extra-settings/assets/icon-128x128.png?rev=1346899"", ""2x"": ""https://ps.w.org/adrotate-extra-settings/assets/icon-256x256.png?rev=1346899""}"
// adrotate-switch	"{""1x"": ""https://ps.w.org/adrotate-switch/assets/icon-128x128.jpg?rev=2153100"", ""2x"": ""https://ps.w.org/adrotate-switch/assets/icon-256x256.jpg?rev=2153100""}"
// ads-adder	"{""default"": ""https://s.w.org/plugins/geopattern-icon/ads-adder.svg""}"
// ads-after-first-paragraph	"{""1x"": ""https://ps.w.org/ads-after-first-paragraph/assets/icon-128x128.png?rev=1606962"", ""2x"": ""https://ps.w.org/ads-after-first-paragraph/assets/icon-256x256.png?rev=1606962""}"
// adroll-for-woocommerce-stores-dev	"{""1x"": ""https://ps.w.org/adroll-for-woocommerce-stores-dev/assets/icon-256x256.png?rev=2909795"", ""2x"": ""https://ps.w.org/adroll-for-woocommerce-stores-dev/assets/icon-256x256.png?rev=2909795""}"
// ads-campaigns	"{""default"": ""https://s.w.org/plugins/geopattern-icon/ads-campaigns.svg""}"
// ads-pixel	"{""1x"": ""https://ps.w.org/ads-pixel/assets/icon-256x256.png?rev=2350147"", ""2x"": ""https://ps.w.org/ads-pixel/assets/icon-256x256.png?rev=2350147""}"
// advanced-custom-fields-markdown	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-markdown_545454.svg""}"
// advanced-custom-fields-limiter-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-limiter-field_f4f4f4.svg""}"
// advanced-custom-fields-menu-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-menu-field.svg""}"
// advanced-custom-fields-menu-field-add-on	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-menu-field-add-on_fbfbfb.svg""}"
// advanced-custom-fields-migrator	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-migrator_fcffff.svg""}"
// advanced-custom-fields-multiple-coordinates	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-multiple-coordinates.svg""}"
// advanced-custom-fields-nextgen-gallery-custom-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-nextgen-gallery-custom-field.svg""}"
// advanced-custom-fields-nextgen-gallery-field-add-on	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-nextgen-gallery-field-add-on_fcfcfd.svg""}"
// adsense-for-authorsafa	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adsense-for-authorsafa_a16fe6.svg""}"
// advanced-custom-fields-nav-menu-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-nav-menu-field_fdfdfd.svg""}"
// advanced-custom-fields-number-slider	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-number-slider.svg""}"
// advanced-custom-fields-oembed-field	"{""default"": ""https://s.w.org/plugins/geopattern-icon/advanced-custom-fields-oembed-field.svg""}"
// advanced-custom-fields-position-field	"{""1x"": ""https://ps.w.org/advanced-custom-fields-position-field/assets/icon.svg?rev=995443"", ""svg"": ""https://ps.w.org/advanced-custom-fields-position-field/assets/icon.svg?rev=995443""}"
// adunblock	"{""default"": ""https://s.w.org/plugins/geopattern-icon/adunblock.svg""}"
// advanced-custom-fields-recaptcha-field	"{""1x"": ""https://ps.w.org/advanced-custom-fields-recaptcha-field/assets/icon-128x128.png?rev=1682143"", ""2x"": ""https://ps.w.org/advanced-custom-fields-recaptcha-field/assets/icon-256x256.png?rev=1682143""}"
// ai-knowledgebase	"{""1x"": ""https://ps.w.org/ai-knowledgebase/assets/icon-128x128.png?rev=3107347"", ""2x"": ""https://ps.w.org/ai-knowledgebase/assets/icon-256x256.png?rev=3107347""}"
// ads-easy-simple-for-ads-into-post	"{""default"": ""https://s.w.org/plugins/geopattern-icon/ads-easy-simple-for-ads-into-post.svg""}"
// ads-exchange	"{""1x"": ""https://ps.w.org/ads-exchange/assets/icon-128x128.png?rev=3163239""}"
// ads-for-visual-composer	"{""1x"": ""https://ps.w.org/ads-for-visual-composer/assets/icon-128x128.png?rev=2766235"", ""2x"": ""https://ps.w.org/ads-for-visual-composer/assets/icon-256x256.png?rev=2766235""}"
// ads-for-wp	"{""1x"": ""https://ps.w.org/ads-for-wp/assets/icon-128x128.png?rev=1919470"", ""2x"": ""https://ps.w.org/ads-for-wp/assets/icon-256x256.png?rev=1919470""}"
// abundatrade-plugin	"{""default"": ""https://s.w.org/plugins/geopattern-icon/abundatrade-plugin.svg""}"
