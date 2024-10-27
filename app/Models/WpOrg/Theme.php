<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $slug
 * @property string $name
 * @property string $version
 * @property string $download_link
 * @property string $requires_php
 * @property CarbonImmutable $last_updated
 * @property CarbonImmutable $creation_time
 * @property string $preview_url
 * @property string $screenshot_url
 * @property array $ratings
 * @property int $rating
 * @property int $num_ratings
 * @property string $reviews_url
 * @property int $downloaded
 * @property int $active_installs
 * @property string $homepage
 * @property array $sections
 * @property array $tags
 * @property array $versions
 * @property array $requires
 * @property bool $is_commercial
 * @property string $external_support_url
 * @property bool $is_community
 * @property string $external_repository_url
 */
class Theme extends BaseModel
{
    use HasUuids;

    protected $table = 'themes';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'version' => 'string',
            'download_link' => 'string',
            'requires_php' => 'string',
            'last_updated' => 'datetime',
            'creation_time' => 'datetime',
            'preview_url' => 'string',
            'screenshot_url' => 'string',
            'ratings' => 'array',
            'rating' => 'integer',
            'num_ratings' => 'integer',
            'reviews_url' => 'string',
            'downloaded' => 'integer',
            'active_installs' => 'integer',
            'homepage' => 'string',
            'sections' => 'array',
            'tags' => 'array',
            'versions' => 'array',
            'requires' => 'array',
            'is_commercial' => 'boolean',
            'external_support_url' => 'string',
            'is_community' => 'boolean',
            'external_repository_url' => 'string',
        ];
    }

    /**
    * Define the relationship to the author
    * @return BelongsTo<Author, $this>
    */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id'); // Use the foreign key column here
    }
}
