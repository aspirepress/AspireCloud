<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Values;

use AspirePress\Cdn\Data\Values\FilePathInterface;

class LocalFilePath implements FilePathInterface
{
    private function __construct(
        private string $path,
    ) {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public static function fromString(string $path): self
    {
        //Assert::fileExists($path);
        return new self($path);
    }
}