<?php

namespace App\Values\WpOrg;

use Bag\Bag;

readonly class Author extends Bag
{
    public function __construct(
        public string $user_nicename,
        public string $profile,
        public string $avatar,
        public string $display_name,
        public string $author,
        public string $author_url,
    ) {}
}
