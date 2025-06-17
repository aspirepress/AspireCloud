<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Themes;

use App\Values\DTO;
use App\Values\WpOrg\PageInfo;
use Bag\Collection;

readonly class QueryThemesResponse extends DTO
{
    public function __construct(
        public Collection $themes,
        public PageInfo $info,
    ) {}

}
