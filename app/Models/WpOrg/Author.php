<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Database\Factories\WpOrg\PluginFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property-read string $id
 * @property string $user_nicename
 * @property string|null $profile
 * @property string|null $avatar
 * @property string|null $display_name
 * @property string|null $author
 * @property string|null $author_url
 */
class Author extends BaseModel
{
    use HasUuids;

    /** @use HasFactory<PluginFactory> */
    use HasFactory;

    protected $table = 'authors';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'user_nicename' => 'string',
            'profile' => 'string',
            'avatar' => 'string',
            'display_name' => 'string',
            'author' => 'string',
            'author_url' => 'string',
        ];
    }
}
