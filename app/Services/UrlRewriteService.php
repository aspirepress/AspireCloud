<?php

declare(strict_types=1);

namespace App\Services;

use App\Utils\Regex;
use App\Values\WpOrg\Plugins\PluginResponse;
use App\Values\WpOrg\Themes\ThemeResponse;
use App\Values\WpOrg\Themes\ThemeUpdateCheckResponse;
use App\Values\WpOrg\Themes\ThemeUpdateData;
use Bag\Collection;
use Bag\Values\Optional;

class UrlRewriteService
{
    public function rewritePluginResponse(PluginResponse $response): PluginResponse
    {
        $out = $response->with([
            'download_link' => $this->rewriteDotOrgUrl($response->download_link),
            'banners' => array_map($this->rewriteDotOrgUrl(...), $response->banners),
            'icons' => array_map($this->rewriteDotOrgUrl(...), $response->icons),
            'screenshots' => $this->rewriteScreenshots($response->screenshots),
            'versions' => array_map($this->rewriteDotOrgUrl(...), $response->versions),
            // TODO: sections -- rewrite urls in description
        ]);

        return $this->fixupDownloadLink($out, $response->download_link);
    }

    public function rewriteThemeResponse(ThemeResponse $response): ThemeResponse
    {
        $out = $response->with([
            'download_link' => $this->rewriteDotOrgUrl($response->download_link),
            'screenshot_url' => $this->rewriteThemeScreenshotUrl($response->screenshot_url),
            'versions' => $this->rewriteThemeVersions($response->versions),
            // TODO: sections -- rewrite urls in description
        ]);

        return $this->fixupDownloadLink($out, $response->download_link);
    }

    public function rewriteThemeUpdateData(ThemeUpdateData $update): ThemeUpdateData
    {
        return $update->with([
            'url' => $this->rewriteDotOrgUrl($update->url),
            'package' => $this->rewriteDotOrgUrl($update->package),
        ]);
    }

    public function rewriteThemeUpdateCheckResponse(ThemeUpdateCheckResponse $response): ThemeUpdateCheckResponse
    {
        return $response->with([
            'themes' => $response->themes->map($this->rewriteThemeUpdateData(...)),
            'no_update' => $response->no_update->map($this->rewriteThemeUpdateData(...)),
        ]);
    }

    public function rewriteDotOrgUrl(string $url): string
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

    public function rewriteScreenshots(array $screenshots): array
    {
        return array_map(
            fn(array $screenshot) => [...$screenshot, 'src' => self::rewriteDotOrgUrl($screenshot['src'] ?? '')],
            $screenshots,
        );
    }

    //  IN: //ts.w.org/wp-content/themes/abhokta/screenshot.png?ver=1.0.0
    // OUT: /download/assets/theme/abhokta/1.0.0/screenshot.png
    public function rewriteThemeScreenshotUrl(string $url): ?string
    {
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

    public function fixupDownloadLink(PluginResponse|ThemeResponse $out, string $fallback): PluginResponse|ThemeResponse
    {
        if (Regex::match('#/(?:plugin|theme)/([^/.]+)\.zip$#i', $out->download_link)) {
            // no dots in the filename before the extension, which means this link isn't useful for caching.
            // replace it with the url for the current version instead, or the unrewritten link if that doesn't exist.
            return $out->with(['download_link' => $out->versions[$out->version] ?? $fallback]);
        }
        return $out;
    }

    public function rewriteThemeVersions(Optional|array $versions): Optional|array
    {
        if ($versions instanceof Optional) {
            return $versions;
        }
        return array_map($this->rewriteDotOrgUrl(...), $versions);
    }
}
