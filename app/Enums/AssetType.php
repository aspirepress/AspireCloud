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

    public function isZip(): bool
    {
        return in_array($this, [self::CORE, self::PLUGIN, self::THEME]);
    }

    public function isAsset(): bool
    {
        return in_array(
            $this,
            [self::PLUGIN_SCREENSHOT, self::PLUGIN_BANNER, self::PLUGIN_GP_ICON, self::THEME_SCREENSHOT],
        );
    }
}
