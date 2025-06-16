<?php

declare(strict_types=1);

namespace App\Integrations\WpOrg;

use Saloon\Enums\Method;
use Saloon\Http\Request;

abstract class AbstractWpOrgRequest extends Request
{
    public function __construct(public readonly string $slug) {}

    protected Method $method = Method::GET;
}
