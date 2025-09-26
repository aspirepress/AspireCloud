<?php

namespace App\Utils;

class Patterns
{
    // overly strict, requires versions to have only 2 or 3 parts
    // public const SEMANTIC_VERSION = '/^v?(0|[1-9]\d*)\.(0|[1-9]\d*)(\.(0|[1-9]\d*))?(-[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?(\+[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?$/';

    // looser and has named groups, but still not actually used anywhere
    public const SEMANTIC_VERSION = '/^v?(?<major>\d+)(?\.(?<minor>\d+))?(?:\.(?<patch>\d+))?(?<extra>[-.\w]*)$/';

    private function __construct()
    {
        // not instantiable
    }
}
