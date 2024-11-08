<?php

namespace App\Models\WpOrg;

use App\Enums\AssetType;
use App\Models\BaseModel;
use Database\Factories\WpOrg\AssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends BaseModel
{
    use HasUuids;

    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    protected $fillable = [
        'asset_type',
        'slug',
        'version',
        'revision',
        'upstream_path',
        'local_path',
    ];

    protected $casts = [
        'asset_type' => AssetType::class,
    ];
}
