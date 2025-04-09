<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Core;

use Bag\Bag;

readonly class CoreUpdateOffer extends Bag
{

    public function __construct(
        public string $response,
        public string $download,
        public string $locale,
        public string $current,
        public string $version,
        public string $php_version,
        public string $mysql_version,
        public string $new_bundled,
        public bool $partial_version,
    ) {}

}
