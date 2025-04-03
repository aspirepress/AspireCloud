<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Themes;

use App\Values\WpOrg\PageInfo;
use Bag\Bag;
use Bag\Collection;

readonly class QueryThemesResponse extends Bag
{
    public function __construct(
        public Collection $themes,
        public PageInfo $info,
    ) {}

}
