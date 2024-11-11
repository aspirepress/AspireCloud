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

    private function __construct()
    {
        // not instantiable
    }
}
