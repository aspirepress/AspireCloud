<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use App\Models\Sync\SyncTheme;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * @property string $id
 * @property string $theme_id
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
            'plugin_id' => 'string',
        ];
    }

    /** @return BelongsTo<Plugin, covariant self> */
    public function plugin(): BelongsTo
    {
        return $this->belongsTo(Plugin::class);
    }

    //endregion

}
