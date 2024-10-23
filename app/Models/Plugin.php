<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
            'added' => 'datetime',
            'last_updated' => 'datetime',
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
