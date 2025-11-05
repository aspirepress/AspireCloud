<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PackageTagFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read string                                                 $id
 * @property-read string                                                 $name
 * @property-read string                                                 $slug
 * @property-read CarbonImmutable|null                                   $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Package> $packages
 */
class PackageTag extends BaseModel
{
    use HasUuids;

    /** @use HasFactory<PackageTagFactory> */
    use HasFactory;

    protected $table = 'package_tags';

    protected $fillable = [
        'slug',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'slug' => 'string',
            'created_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsToMany<Package, $this> */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_package_tag', 'package_tag_id', 'package_id');
    }
}
