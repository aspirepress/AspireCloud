<?php

namespace App\Enums;

use Illuminate\Support\Str;
use InvalidArgumentException;

enum AssetType: string
{
    case CORE = 'core';
    case PLUGIN = 'plugin';
    case THEME = 'theme';
    case PLUGIN_SCREENSHOT = 'plugin-screenshot';
    case PLUGIN_BANNER = 'plugin-banner';
    case THEME_SCREENSHOT = 'theme-screenshot';

    public static function fromPath(string $path): self
    {
        // WordPress core downloads
        if (\Safe\preg_match('/wordpress-[\d.]+\.zip$/', $path)) {
            return self::CORE;
        }

        // Screenshots and Banners)
        if (Str::contains($path, '/assets/')) {
            if (Str::contains($path, 'screenshot-')) {
                return self::PLUGIN_SCREENSHOT;
            }
            if (Str::contains($path, 'banner-')) {
                return self::PLUGIN_BANNER;
            }
        }

        // plugin and theme zips files
        if (Str::endsWith($path, '.zip')) {
            if (Str::contains($path, '/theme/')) {
                return self::THEME;
            }
            if (Str::contains($path, '/plugin/')) {
                return self::PLUGIN;
            }
        }

        throw new InvalidArgumentException("Unknown asset type for path: {$path}");
    }

    public function getBasePath(): string
    {
        return match ($this) {
            self::CORE => 'core',
            self::PLUGIN => 'plugins',
            self::THEME => 'themes',
            self::PLUGIN_SCREENSHOT,
            self::PLUGIN_BANNER => 'assets',
        };
    }

    public function isZip(): bool
    {
        return in_array($this, [self::CORE, self::PLUGIN, self::THEME]);
    }

    public function isAsset(): bool
    {
        return in_array($this, [self::PLUGIN_SCREENSHOT, self::PLUGIN_BANNER]);
    }

    public function getUpstreamBaseUrl(): string
    {
        return match ($this) {
            self::CORE => 'https://wordpress.org/',
            self::PLUGIN => 'https://downloads.wordpress.org/plugin/',
            self::THEME => 'https://downloads.wordpress.org/theme/',
            self::PLUGIN_SCREENSHOT,
            self::PLUGIN_BANNER => 'https://ps.w.org/%s/assets/',
        };
    }
}
