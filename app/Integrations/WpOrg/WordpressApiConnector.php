<?php

declare(strict_types=1);

namespace App\Integrations\WpOrg;

use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\HasPagination;
use Saloon\Traits\Plugins\HasTimeout;

class WordpressApiConnector extends Connector implements HasPagination
{
    use HasTimeout;

    protected int $connectTimeout = 10;
    protected int $requestTimeout = 120;

    public function resolveBaseUrl(): string
    {
        return 'https://api.wordpress.org';
    }

    public function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'User-Agent' => 'WordPress/6.8; https://example.org',
        ];
    }

    public function paginate(Request $request): WpOrgPaginator
    {
        return new WpOrgPaginator($this, $request);
    }
}
