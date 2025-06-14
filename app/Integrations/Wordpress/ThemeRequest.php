<?php

declare(strict_types=1);

namespace App\Integrations\Wordpress;

class ThemeRequest extends AbstractWordpressApiRequest
{
    public function resolveEndpoint(): string
    {
        return '/themes/info/1.2';
    }

    public function defaultQuery(): array
    {
        return [
            'action' => 'theme_information',
            'slug' => $this->slug,
            'fields' => [
                'description',
                'sections',
                'rating',
                'ratings',
                'downloaded',
                'download_link',
                'last_updated',
                'homepage',
                'tags',
                'template',
                'parent',
                'versions',
                'screenshot_url',
                'active_installs',
            ],
        ];
    }
}
