<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageRelease extends BaseModel
{
    use HasUuids;

    protected $table = 'package_releases';

    protected function casts(): array
    {
        return [
            'id'           => 'string',
            'package_id'   => 'string',
            'version'      => 'string',
            'download_url' => 'string',
            'signature'    => 'string',
            'checksum'     => 'string',

            'requires'     => 'array',
            'suggests'     => 'array',
            'provides'     => 'array',
            'artifacts'    => 'array',

            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
