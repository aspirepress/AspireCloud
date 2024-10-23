<?php

namespace App\Data\WpOrg;

use Spatie\LaravelData\Data;

class Author extends Data
{
    public function __construct(
        public readonly string $user_nicename,
        public readonly string $profile,
        public readonly string $avatar,
        public readonly string $display_name,
        public readonly string $author,
        public readonly string $author_url,
    ) {}
}
