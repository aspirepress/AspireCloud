<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use App\Models\Sync\SyncTheme;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

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
            'theme_id' => 'string',
        ];
    }

    /** @return BelongsTo<Theme, covariant self> */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    //endregion

}
