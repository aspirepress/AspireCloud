<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Database\Factories\WpOrg\PluginTagFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read string $id
 * @property-read string $slug
 * @property-read string $name
 * @property-read Collection<int,Plugin> $plugins
 */
final class PluginTag extends BaseModel
{
    //region Model Definition

    use HasUuids;

    /** @use HasFactory<PluginTagFactory> */
    use HasFactory;

    protected $table = 'plugin_tags';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
        ];
    }

    //endregion

    //region Relationships

    /**
     * Define the relationship to plugins.
     *
     * @return BelongsToMany<Plugin, $this>
     */
    public function plugins(): BelongsToMany
    {
        return $this->belongsToMany(Plugin::class, 'plugin_plugin_tags', 'plugin_tag_id', 'plugin_id');
    }

    //endregion
}
