<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Entities;

use AspirePress\Cdn\Data\Values\FilePathInterface;
use AspirePress\Cdn\Data\Values\FileUrlInterface;
use Ramsey\Uuid\UuidInterface;

class DownloadableFile
{
    private function __construct(
        private UuidInterface $id,
        private string $fileName,
        private ?FilePathInterface $filePath,
        private ?FileUrlInterface $fileUrl
    )
    {
    }

    public static function fromValues(UuidInterface $id, string $fileName, ?FilePathInterface $filePath, ?FileUrlInterface $fileUrl): self
    {
        return new self($id, $fileName, $filePath, $fileUrl);
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getFilePath(): ?FilePathInterface
    {
        return $this->filePath;
    }

    public function getFileUrl(): ?FileUrlInterface
    {
        return $this->fileUrl;
    }


}