<?php

declare(strict_types=1);

namespace App\Integrations\WpOrg;

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\PaginationPlugin\PagedPaginator;

class WpOrgPaginator extends PagedPaginator
{
    protected function isLastPage(Response $response): bool
    {
        return $response->json('info.page') >= $response->json('info.pages');
    }

    protected function getPageItems(Response $response, Request $request): array
    {
        $endpoint = $request->resolveEndpoint();
        $key = match (true) {
            str_starts_with($endpoint, '/plugins') => 'plugins',
            str_starts_with($endpoint, '/themes') => 'themes',
            default => throw new \RuntimeException('Unknown endpoint'),
        };
        return $response->json($key);
    }
}
