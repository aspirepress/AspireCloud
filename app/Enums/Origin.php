<?php

namespace App\Enums;

enum Origin: string
{
    case FAIR = 'fair';
    case WP = 'wp';

    /**
     * Get the list of origin values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
