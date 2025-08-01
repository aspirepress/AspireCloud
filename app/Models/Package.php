<?php

namespace App\Models;

use App\Values\Packages\PackageData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Package extends BaseModel
{
    use HasUuids;

    protected $table = 'packages';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'did' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'description' => 'string',
            'download_url' => 'string',
            'version' => 'string',
            'origin_id' => 'string',
            'package_type_id' => 'string',
            'raw_metadata' => 'array',
        ];
    }

    public static function fromPackageData(PackageData $packageData): self
    {
        $origin = Origin::where('slug', $packageData->origin)->first();
        if (!$origin) {
            throw new \InvalidArgumentException("Origin not found: {$packageData->origin}");
        }

        $type = $origin->packageTypes()->where('slug', $packageData->type)->first();
        if (!$type) {
            throw new \InvalidArgumentException("Package type not found: {$packageData->type}");
        }

        $package = self::_create([
            'did' => $packageData->did,
            'name' => $packageData->name,
            'slug' => $packageData->slug,
            'description' => $packageData->description,
            'download_url' => $packageData->download_url,
            'version' => $packageData->version,
            'origin_id' => $origin->id,
            'package_type_id' => $type->id,
            'raw_metadata' => $packageData->raw_metadata,
        ]);

        // @todo - authors

        return $package;
    }
}
