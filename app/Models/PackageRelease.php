<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string                        $id
 * @property-read string                        $package_id
 * @property-read string                        $version
 * @property-read string|null                   $download_url
 * @property-read array<string, mixed>|null     $requires
 * @property-read array<string, mixed>|null     $suggests
 * @property-read array<string, mixed>|null     $provides
 * @property-read array<string, mixed>|null     $artifacts
 * @property-read string|null                   $signature
 * @property-read string|null                   $checksum
 * @property-read CarbonImmutable|null          $created_at
 * @property-read Package|null                  $package
 */
class PackageRelease extends BaseModel
{
    use HasUuids;

    public const UPDATED_AT = null;

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

            'created_at'   => 'immutable_datetime',
        ];
    }

    /**
     * Get the package that owns the release.
     *
     * @return BelongsTo<Package, $this>
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
