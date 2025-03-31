<?php

namespace App\Values\WpOrg;

use Bag\Bag;

readonly class PageInfo extends Bag
{
    public function __construct(
        public int $page,
        public int $pages,
        public int $results,
    ) {}
}
