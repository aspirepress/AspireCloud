<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\ClosedPluginFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read string $id
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $description
 * @property-read CarbonImmutable $closed_date
 * @property-read string $reason
 * @property-read array|null $metadata
 * @property-read CarbonImmutable $ac_created
 * @property-read string $ac_shadow_id          if previously open plugin exists, this is its id.  usually null.
 */
class ClosedPlugin extends BaseModel
{
    /** @use HasFactory<ClosedPluginFactory> */
    use HasFactory;
    protected $table = 'closed_plugins';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'description' => 'string',
            'closed_date' => 'immutable_datetime',
            'reason' => 'string',
            'metadata' => 'array',
            'ac_created' => 'immutable_datetime',
            'ac_shadow_id' => 'string',
        ];
    }
}
