<?php

namespace App\Data\WpOrg\Themes;

use App\Data\WpOrg\PageInfo;
use Spatie\LaravelData\Data;

class QueryThemesResponse extends Data
{
    public function __construct(
        public readonly PageInfo $pageInfo,
        // TODO
    )
    {
    }
}
