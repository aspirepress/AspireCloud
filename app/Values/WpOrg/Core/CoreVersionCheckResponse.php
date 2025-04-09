<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Core;

use Bag\Bag;
use Illuminate\Support\Collection;

readonly class CoreVersionCheckResponse extends Bag
{

    /**
     * @param Collection<int, CoreUpdateOffer> $offers
     * @param Collection<int, CoreTranslationOffer> $translations
     */
    public function __construct(
        public Collection $offers,
        public Collection $translations,
    ) {}
}
