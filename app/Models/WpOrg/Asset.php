<?php

namespace App\Models\WpOrg;

use App\Enums\AssetType;
use App\Events\AssetCreated;
use Database\Factories\WpOrg\AssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasUuids;

    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'created' => AssetCreated::class,
    ];

    protected $fillable = [
        'asset_type',
        'slug',
        'version',
        'revision',
        'upstream_path',
        'local_path',
        'repository',
    ];

    protected $casts = [
        'asset_type' => AssetType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
