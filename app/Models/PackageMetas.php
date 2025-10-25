<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string                     $id
 * @property-read string                     $package_id
 * @property-read array<string, mixed>       $metadata
 * @property-read CarbonImmutable|null       $created_at
 * @property-read Package|null               $package
 */
class PackageMetas extends BaseModel
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'package_metas';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'package_id' => 'string',
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
