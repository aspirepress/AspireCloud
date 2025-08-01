<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Origin extends Model
{
    use HasUuids;

    /**
     * Get the package types associated with the origin.
     *
     * @phpstan-return HasMany<PackageType, $this>
     */
    public function packageTypes(): HasMany
    {
        return $this->hasMany(PackageType::class);
    }
}
