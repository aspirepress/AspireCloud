<?php

declare(strict_types=1);

namespace App\Values;

use Bag\Bag;

abstract readonly class DTO extends Bag
{
    use ToJsonPretty;
}
