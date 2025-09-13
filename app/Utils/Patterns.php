<?php

namespace App\Utils;

class Patterns
{
    public const SEMANTIC_VERSION = '/^v?(0|[1-9]\d*)\.(0|[1-9]\d*)(\.(0|[1-9]\d*))?(-[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?(\+[0-9A-Za-z-]+(\.[0-9A-Za-z-]+)*)?$/';

    private function __construct()
    {
        // not instantiable
    }
}
