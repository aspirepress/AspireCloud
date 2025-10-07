<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Bag;
use Bag\Collection as BagCollection;
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

    /**
     * I'm convinced the type of iterable<int,mixed> in Bag's WithCollections::collect is a bug, so this overrides it
     *
     * @param iterable<array-key,mixed> $values (widened from parent's iterable<int,mixed>)
     * @return BagCollection<array-key,static>
     * @todo see if this is still the case in latest release of dshafik/bag and report it as a bug if so
     *
     */
    #[Override]
    public static function collect(iterable $values = []): BagCollection
    {
        return parent::collect($values); // @mago-expect analysis:less-specific-argument
    }
}
