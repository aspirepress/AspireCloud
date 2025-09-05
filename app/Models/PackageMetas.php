<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageMetas extends BaseModel
{
    use HasUuids;

    protected $table = 'package_metas';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'package_id' => 'string',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
