<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\Data\Values;

use Psr\Http\Message\UriInterface;

interface FileUrlInterface
{
    public function getUrlString(): string;

    public function getUri(): UriInterface;
}
