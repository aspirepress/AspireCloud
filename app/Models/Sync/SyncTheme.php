<?php

namespace App\Models\Sync;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $current_version
 * @property string $updated
 * @property string $pulled_at
 * @property array|null $metadata
 */
class SyncTheme extends BaseModel
{
    use HasUuids;

    protected $table = 'sync_themes';

    /** @return HasMany<SyncThemeFile, covariant static> */
    public function files(): HasMany
    {
        return $this->hasMany(SyncThemeFile::class, 'theme_id');
    }

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'file_url' => 'string',
            'type' => 'string',
            'version' => 'string',
            'metadata' => 'array',
            'created' => 'immutable_datetime',
            'processed' => 'immutable_datetime',
            'hash' => 'string',
        ];
    }
}
