<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property-read string $id
 * @property-read string $user_nicename
 * @property-read string|null $profile
 * @property-read string|null $avatar
 * @property-read string|null $display_name
 * @property-read string|null $author
 * @property-read string|null $author_url
 */
class Author extends BaseModel
{
    use HasUuids;

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
