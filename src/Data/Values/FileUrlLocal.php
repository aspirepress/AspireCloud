<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Values;

use Laminas\Diactoros\Uri;
use Psr\Http\Message\UriInterface;
use Webmozart\Assert\Assert;

class FileUrlLocal implements FileUrlInterface
{
    private function __construct(
        private UriInterface $uri,
    ) {
    }

    public function getUrlString(): string
    {
        return (string) $this->uri;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public static function fromUrl(string $url): self
    {
        $uri = new Uri($url);

        Assert::notEmpty($uri->getPath());

        return new self($uri);
    }
}
