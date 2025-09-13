<?php

namespace App\Enums;

enum PackageType: string
{
    case CORE = 'wp-core';
    case PLUGIN = 'wp-plugin';
    case THEME = 'wp-theme';

    /**
     * Get the list of package type values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
