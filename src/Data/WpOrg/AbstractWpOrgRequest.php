<?php

namespace App\Data\WpOrg;

use Symfony\Component\HttpFoundation\Request;

abstract readonly class AbstractWpOrgRequest {
    abstract public static function fromRequest(Request $request): static;
}
