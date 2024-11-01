<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $id
 * @property string $theme_id
 * @property string $slug
 */
final class ThemeTag extends BaseModel
{
    //region Model Definition

    use HasUuids;

    protected $table = 'theme_tags';

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
     * Define the relationship to themes.
     *
     * @return BelongsToMany<Theme, covariant self>
     */
    public function themes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, 'theme_theme_tags', 'theme_tag_id', 'theme_id');
    }

    //endregion
}
