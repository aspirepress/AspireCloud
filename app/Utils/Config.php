<?php

namespace App\Utils;

class Config
{
    /** @return string[] */
    public static function stringList(string $input, string $delimiter = ','): array
    {
        return collect(explode($delimiter, $input))
            ->map(fn(string $item) => trim($item))
            ->filter(fn(string $item) => $item !== '')
            ->values()
            ->toArray();
    }

    private function __construct()
    {
        // not instantiable
    }
}
