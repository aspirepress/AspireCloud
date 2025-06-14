<?php

declare(strict_types=1);

namespace App\Integrations\Wordpress;

class PluginRequest extends AbstractWordpressApiRequest
{
    public function resolveEndpoint(): string
    {
        return '/plugins/info/1.2';
    }

    public function defaultQuery(): array
    {
        return [
            'action' => 'plugin_information',
            'slug' => $this->slug,
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
