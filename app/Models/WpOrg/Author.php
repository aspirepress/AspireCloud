<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\PluginFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property string $user_nicename
 * @property string $profile
 * @property string $avatar
 * @property string $display_name
 * @property string $author
 * @property string $author_url
 */
class Author extends BaseModel
{
    use HasUuids;

    /** @use HasFactory<PluginFactory> */
    use HasFactory;

    protected $table = 'authors';


}
