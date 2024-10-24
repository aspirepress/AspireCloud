<?php

namespace App\Data\WpOrg;

readonly class PageInfo
{
    public function __construct(
        public int $page,
        public int $pages,
        public int $results,
    ) {}

    // TODO: use symfony/serializer instead
    public function toArray(): array {
        return ['page' => $this->page, 'pages' => $this->pages, 'results' => $this->results];
    }
}
