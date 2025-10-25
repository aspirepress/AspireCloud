<?php
declare(strict_types=1);

namespace App\Utils;

class Regex
{
    /** @return string[] */
    public static function match(string $pattern, string $subject): array
    {
        $matches = [];
        \Safe\preg_match($pattern, $subject, $matches);
        return $matches ?? []; // $matches cannot be null, but mago thinks otherwise ¯\_(ツ)_/¯
    }

    public static function replace(string $pattern, string $replacement, string $subject, int $limit = -1): string
    {
        $result = \Safe\preg_replace($pattern, $replacement, $subject, $limit);
        assert(is_string($result)); // cannot be otherwise when the parameters are strings
        return $result;
    }

    private function __construct()
    {
        // not instantiable
    }
}
