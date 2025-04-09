<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Core;

use Bag\Bag;
use DateTimeInterface;

readonly class CoreTranslationOffer extends Bag
{
    public function __construct(
        public string $type,
        public string $slug,
        public string $language,
        public string $version,
        public DateTimeInterface $updated,
        public string $package,
        public bool $autoupdate,
    ) {}
}
