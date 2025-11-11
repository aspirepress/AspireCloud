<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property-read int    $id
 * @property-read string $key
 * @property-read int    $value
 **/
class Metric extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'integer',
    ];
}
