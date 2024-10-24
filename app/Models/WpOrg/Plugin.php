<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $short_description
 * @property string $description
 * @property string $version
 * @property string $author
 * @property string $requires
 * @property string $requires_php
 * @property string $tested
 * @property string $download_link
 * @property CarbonImmutable $added
 * @property CarbonImmutable $last_updated
 * @property string $author_profile
 * @property int $rating
 * @property array $ratings
 * @property int $num_ratings
 * @property int $support_threads
 * @property int $support_threads_resolved
 * @property int $active_installs
 * @property int $downloaded
 * @property string $homepage
 * @property array $banners
 * @property array $tags
 * @property string $donate_link
 * @property array $contributors
 * @property array $icons
 * @property array $source
 * @property string $business_model
 * @property string $commercial_support_url
 * @property string $support_url
 * @property string $preview_link
 * @property string $repository_url
 * @property array $requires_plugins
 * @property array $compatibility
 * @property array $screenshots
 * @property array $sections
 * @property array $versions
 * @property array $upgrade_notice
 */
class Plugin extends BaseModel
{
    use HasUuids;

    protected $table = 'plugins';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'short_description' => 'string',
            'description' => 'string',
            'version' => 'string',
            'author' => 'string',
            'requires' => 'string',
            'requires_php' => 'string',
            'tested' => 'string',
            'download_link' => 'string',
            'added' => 'immutable_datetime',
            'last_updated' => 'immutable_datetime',
            'author_profile' => 'string',
            'rating' => 'integer',
            'ratings' => 'array',
            'num_ratings' => 'integer',
            'support_threads' => 'integer',
            'support_threads_resolved' => 'integer',
            'active_installs' => 'integer',
            'downloaded' => 'integer',
            'homepage' => 'string',
            'banners' => 'array',
            'tags' => 'array',
            'donate_link' => 'string',
            'contributors' => 'array',
            'icons' => 'array',
            'source' => 'array',
            'business_model' => 'string',
            'commercial_support_url' => 'string',
            'support_url' => 'string',
            'preview_link' => 'string',
            'repository_url' => 'string',
            'requires_plugins' => 'array',
            'compatibility' => 'array',
            'screenshots' => 'array',
            'sections' => 'array',
            'versions' => 'array',
            'upgrade_notice' => 'array',
        ];
    }
}
