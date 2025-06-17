<?php

namespace App\Values\WpOrg;

use App\Values\DTO;

readonly class PageInfo extends DTO
{
    public function __construct(
        public int $page,
        public int $pages,
        public int $results,
    ) {}
}
