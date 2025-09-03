<?php

namespace App\Models;

use App\Models\Package;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Tag extends Model
{
    use HasUuids;

    protected $table = 'tags';

    protected $fillable = [
        'slug',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'id'             => 'string',
            'taggable_id'    => 'string',
            'taggable_type'  => 'string',
            'name'           => 'string',
            'slug'           => 'string',
            'created_at'     => 'datetime',
            'updated_at'     => 'datetime',
        ];
    }

    /**
     * Get the parent taggable model.
     *
     * @return MorphTo
     * @phpstan-return MorphTo<Package, Tag>
     */
    public function taggable(): MorphTo
    {
        /** @var MorphTo<Package, Tag> $relation */
        $relation = $this->morphTo();

        return $relation;
    }
}
