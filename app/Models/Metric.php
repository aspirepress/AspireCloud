<?php

namespace App\Models;

use App\Models\BaseModel;

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
