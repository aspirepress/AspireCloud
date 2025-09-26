<?php

declare(strict_types=1);

namespace App\Values;

/**
 * In addition to adding the JSON_PRETTY_PRINT and JSON_UNESCAPED_* flags, this also sets JSON_THROW_ON_ERROR,
 * which any sane consumer of this traight ought to be doing in the first place.
 */
trait ToJsonPretty
{
    public const DEFAULT_FLAGS = JSON_THROW_ON_ERROR
    | JSON_PRETTY_PRINT
    | JSON_UNESCAPED_SLASHES
    | JSON_UNESCAPED_UNICODE;

    public function toJson($options = 0): string|false
    {
        return parent::toJson($options | self::DEFAULT_FLAGS);
    }
}
