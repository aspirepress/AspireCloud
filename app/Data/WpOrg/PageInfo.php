<?php

namespace App\Data\WpOrg;

use Spatie\LaravelData\Data;

class PageInfo extends Data
{
    public function __construct(
        public readonly int $page,
        public readonly int $pages,
        public readonly int $results,
    ) {}
}
