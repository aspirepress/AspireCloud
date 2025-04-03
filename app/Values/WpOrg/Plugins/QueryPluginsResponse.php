<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Plugins;

use App\Values\WpOrg\PageInfo;
use Bag\Bag;
use Bag\Collection;

readonly class QueryPluginsResponse extends Bag
{
    public function __construct(
        public Collection $plugins,
        public PageInfo $info,
    ) {}

}
