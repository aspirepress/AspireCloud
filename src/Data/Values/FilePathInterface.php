<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Values;

interface FilePathInterface
{
    public function getPath(): string;
}