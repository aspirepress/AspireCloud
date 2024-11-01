<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $plugi
 * @property string $slug
 */
final class PluginTag extends BaseModel
{
    //region Model Definition

    use HasUuids;

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
     * @return BelongsToMany<Plugin, covariant self>
     */
    public function plugins(): BelongsToMany
    {
        return $this->belongsToMany(Plugin::class, 'plugin_plugin_tags', 'tag_id', 'plugin_id');
    }

    //endregion
}
