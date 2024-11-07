<?php

namespace App\Enums;

use Illuminate\Support\Str;
use InvalidArgumentException;

enum AssetType: string
{
    case CORE_ZIP = 'core_zip';
    case PLUGIN_ZIP = 'plugin_zip';
    case THEME_ZIP = 'theme_zip';
    case SCREENSHOT = 'screenshot';
    case BANNER = 'banner';

    public static function fromPath(string $path): self
    {
        // WordPress core downloads
        if (\Safe\preg_match('/wordpress-[\d.]+\.zip$/', $path)) {
            return self::CORE_ZIP;
        }

        // Screenshots and Banners)
        if (Str::contains($path, '/assets/')) {
            if (Str::contains($path, 'screenshot-')) {
                return self::SCREENSHOT;
            }
            if (Str::contains($path, 'banner-')) {
                return self::BANNER;
            }
        }

        // plugin and theme zips files
        if (Str::endsWith($path, '.zip')) {
            if (Str::contains($path, '/theme/')) {
                return self::THEME_ZIP;
            }
            if (Str::contains($path, '/plugin/')) {
                return self::PLUGIN_ZIP;
            }
        }

        throw new InvalidArgumentException("Unknown asset type for path: {$path}");
    }

    public function getBasePath(): string
    {
        return match ($this) {
            self::CORE_ZIP => 'core',
            self::PLUGIN_ZIP => 'plugins',
            self::THEME_ZIP => 'themes',
            self::SCREENSHOT,
            self::BANNER => 'assets',
        };
    }

    public function isZip(): bool
    {
        return in_array($this, [self::CORE_ZIP, self::PLUGIN_ZIP, self::THEME_ZIP]);
    }

    public function isAsset(): bool
    {
        return in_array($this, [self::SCREENSHOT, self::BANNER]);
    }

    public function getUpstreamBaseUrl(): string
    {
        return match ($this) {
            self::CORE_ZIP => 'https://wordpress.org/',
            self::PLUGIN_ZIP => 'https://downloads.wordpress.org/plugin/',
            self::THEME_ZIP => 'https://downloads.wordpress.org/theme/',
            self::SCREENSHOT,
            self::BANNER => 'https://ps.w.org/%s/assets/',
        };
    }
}
