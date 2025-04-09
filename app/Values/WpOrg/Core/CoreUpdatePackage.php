<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Core;

use Bag\Bag;

readonly class CoreUpdatePackage extends Bag
{
    public function __construct(
        public string $full,
        public string|false $no_content,
        public string|false $new_bundled,
        public string|false $partial,
        public string|false $rollback,
    ) {}
}
