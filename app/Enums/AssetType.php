<?php

namespace App\Enums;

enum AssetType: string
{
    case CORE = 'core';
    case PLUGIN = 'plugin';
    case THEME = 'theme';
    case PLUGIN_SCREENSHOT = 'plugin-screenshot';
    case PLUGIN_BANNER = 'plugin-banner';
    case PLUGIN_GP_ICON = 'plugin-gp-icon'; // geopattern-icon only -- other icons are treated as screenshots
    case THEME_SCREENSHOT = 'theme-screenshot';

    public function isImage(): bool
    {
        return in_array(
            $this,
            [self::PLUGIN_SCREENSHOT, self::PLUGIN_BANNER, self::PLUGIN_GP_ICON, self::THEME_SCREENSHOT],
        );
    }

    public function buildUpstreamUrl(string $slug, string $file, ?string $revision): string
    {
        $baseUrl = match ($this) {
            self::CORE => 'https://wordpress.org/',
            self::PLUGIN => 'https://downloads.wordpress.org/plugin/',
            self::THEME => 'https://downloads.wordpress.org/theme/',
            self::PLUGIN_SCREENSHOT,
            self::PLUGIN_BANNER => "https://ps.w.org/$slug/assets/",
            self::PLUGIN_GP_ICON => "https://s.w.org/plugins/geopattern-icon/",
            self::THEME_SCREENSHOT => "https://ts.w.org/wp-content/themes/$slug/",
        };

        $url = $baseUrl . $file;

        if ($revision && $this->isImage()) {
            $url .= "?rev={$revision}";
        }

        return $url;
    }
}
