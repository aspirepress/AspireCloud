<?php

declare(strict_types=1);

namespace App\Values;

use App\Utils\ToJsonPretty;
use Bag\Bag;

abstract readonly class DTO extends Bag
{
    use ToJsonPretty;
}
