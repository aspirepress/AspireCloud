<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Bag;
use Override;

abstract readonly class DTO extends Bag
{
    public const DEFAULT_JSON_FLAGS =
        JSON_THROW_ON_ERROR
        | JSON_PRETTY_PRINT
        | JSON_UNESCAPED_SLASHES
        | JSON_UNESCAPED_UNICODE;

    #[Override]
    public function toJson($options = 0): string|false
    {
        return parent::toJson($options | static::DEFAULT_JSON_FLAGS);
    }
}
