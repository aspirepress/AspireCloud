<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    // use HasUuids;  // can't be undone, so it's better to define on each subclass

    public $timestamps = false;
}
