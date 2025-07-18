<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Export;

use App\Values\DTO;
use Bag\Attributes\Validation\In;
use Bag\Attributes\Validation\Regex;
use Bag\Attributes\StripExtraParameters;
use Bag\Attributes\Laravel\FromRouteParameter;

#[StripExtraParameters]
readonly class ExportRequest extends DTO
{
    public function __construct(
        #[FromRouteParameter('type')]
        #[In('plugins', 'themes', 'closed_plugins')]
        public string $type,
        #[Regex('/^\d{4}-\d{2}-\d{2}$/')]
        public ?string $after = null,
    ) {}
}
