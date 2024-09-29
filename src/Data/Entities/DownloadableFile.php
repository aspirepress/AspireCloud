<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Entities;

use AspirePress\Cdn\Data\Values\FilePathInterface;
use AspirePress\Cdn\Data\Values\FileUrlInterface;
use AspirePress\Cdn\Data\Values\FileUrlLocal;
use AspirePress\Cdn\Data\Values\LocalFilePath;
use AspirePress\Cdn\Data\Values\Version;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

class DownloadableFile
{
    private function __construct(
        private UuidInterface $id,
        private UuidInterface $pluginId,
        private string $fileName,
        private string $type,
        private Version $version,
        private ?FilePathInterface $filePath,
        private ?FileUrlInterface $fileUrl
    ) {
    }

    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'id');
        Assert::keyExists($data, 'plugin_id');
        Assert::keyExists($data, 'filename');
        Assert::keyExists($data, 'file_path');
        Assert::keyExists($data, 'file_url');
        Assert::keyExists($data, 'type');
        Assert::keyExists($data, 'version');

        Assert::uuid($data['id']);
        Assert::uuid($data['plugin_id']);
        Assert::notEmpty($data['filename']);
        Assert::notEmpty($data['file_path']);
        Assert::oneOf($data['type'], ['local', 'cdn']);
        Assert::keyExists($data, 'version');

        $version  = Version::fromString($data['version']);
        $filePath = LocalFilePath::fromString($data['file_path']);

        if ($data['file_url'] !== null) {
            $fileUrl = FileUrlLocal::fromUrl($data['file_url']);
        } else {
            $fileUrl = null;
        }

        return new self(
            Uuid::fromString($data['id']),
            Uuid::fromString($data['plugin_id']),
            $data['filename'],
            $data['type'],
            $version,
            $filePath,
            $fileUrl
        );
    }

    public static function fromValues(UuidInterface $id, UuidInterface $pluginId, string $fileName, string $type, ?FilePathInterface $filePath, ?FileUrlInterface $fileUrl): self
    {
        Assert::oneOf($type, ['local', 'cdn']);
        return new self($id, $pluginId, $fileName, $type, $filePath, $fileUrl);
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
