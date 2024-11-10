<?php

namespace App\Utils;

class Regex
{
    public static function match($pattern, $subject): array
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
