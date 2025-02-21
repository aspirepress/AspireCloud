<?php

namespace App\Utils;

class Regex
{
    /** @return string[] */
    public static function match(string $pattern, string $subject): array
    {
        $matches = [];
        \Safe\preg_match($pattern, $subject, $matches);
        return $matches;
    }

    public static function replace(string $pattern, string $replacement, string $subject, int $limit = -1): string
    {
        return \Safe\preg_replace($pattern, $replacement, $subject, $limit);
    }

    private function __construct()
    {
        // not instantiable
    }
}
