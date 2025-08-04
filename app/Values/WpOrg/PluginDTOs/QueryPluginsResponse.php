<?php

declare(strict_types=1);

namespace App\Values\WpOrg\PluginDTOs;

use App\Values\DTO;
use App\Values\WpOrg\PageInfo;
use Bag\Collection;

readonly class QueryPluginsResponse extends DTO
{
    public function __construct(
        public Collection $plugins,
        public PageInfo $info,
    ) {}

}
