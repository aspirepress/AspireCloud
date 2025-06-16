<?php

declare(strict_types=1);

namespace App\Integrations\WpOrg;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\PaginationPlugin\Contracts\Paginatable;

class QueryPluginsRequest extends Request implements Paginatable
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/plugins/info/1.2';
    }

    public function defaultQuery(): array
    {
        return [
            'action' => 'query_plugins',
            'browse' => 'updated',
            'fields' => [
                'active_installs',
                'added',
                'author',
                'author_block_count',
                'author_block_rating',
                'author_profile',
                'banners',
                'compatibility',
                'contributors',
                'description',
                'donate_link',
                'download_link',
                'downloaded',
                'homepage',
                'icons',
                'last_updated',
                'name',
                'num_ratings',
                'rating',
                'ratings',
                'requires',
                'requires_php',
                'screenshots',
                'sections',
                'short_description',
                'slug',
                'support_threads',
                'support_threads_resolved',
                'tags',
                'tested',
                'version',
                'versions',
            ],
        ];
    }
}
