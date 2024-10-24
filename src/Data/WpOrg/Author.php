<?php

namespace App\Data\WpOrg;

readonly class Author
{
    public function __construct(
        public string $user_nicename,
        public string $profile,
        public string $avatar,
        public string $display_name,
        public string $author,
        public string $author_url,
    ) {}

    // TODO: replace with symfony/serializer
    public function toArray(): array {
        return [
            'user_nicename' => $this->user_nicename,
            'profile' => $this->profile,
            'avatar' => $this->avatar,
            'display_name' => $this->display_name,
            'author' => $this->author,
            'author_url' => $this->author_url,
        ];
    }
}

