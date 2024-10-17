<?php

declare(strict_types=1);

namespace AspirePress\AspireCloud\Data\Entities;

use AspirePress\AspireCloud\Data\Enums\AsString;
use AspirePress\AspireCloud\Data\Values\Version;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class Plugin
{
    private function __construct(
        private UuidInterface $id,
        private string $name,
        private string $slug,
        private Version $currentVersion,
        private ?DownloadableFile $file
    ) {
    }

    /**
     * @param array<string, string> $data
     */
    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'id');
        Assert::keyExists($data, 'name');
        Assert::keyExists($data, 'slug');
        Assert::keyExists($data, 'current_version');

        Assert::uuid($data['id']);
        Assert::string($data['name']);
        Assert::string($data['slug']);
        Assert::string($data['current_version']);

        if (isset($data['file'])) {
            Assert::isInstanceOf($data['file'], DownloadableFile::class);
            $file = $data['file'];
        }

        return new self(
            Uuid::fromString($data['id']),
            $data['name'],
            $data['slug'],
            Version::fromString($data['current_version']),
            $file ?? null
        );
    }

    public static function fromValues(UuidInterface $id, string $name, string $slug, Version $currentVersion, ?DownloadableFile $file = null): self
    {
        Assert::notEmpty($name);
        Assert::notEmpty($slug);

        return new self(
            $id,
            $name,
            $slug,
            $currentVersion,
            $file
        );
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getCurrentVersion(): Version
    {
        return $this->currentVersion;
    }

    public function getFile(): ?DownloadableFile
    {
        return $this->file;
    }

    public function newerVersionAvailable(Version|string $version): bool
    {
        return $this->currentVersion->versionNewerThan($version);
    }

    /**
     * @return array<string, string|array<string, string>>
     */
    public function toArray(AsString $asString = AsString::NO): array
    {
        $id      = $asString === AsString::YES ? (string) $this->id : $this->id;
        $version = $asString === AsString::YES ? (string) $this->currentVersion : $this->currentVersion;
        $file    = $asString === AsString::YES && $this->getFile() ? $this->getFile()->toArray($asString) : $this->getFile();

        return [
            'id'              => $id,
            'name'            => $this->getName(),
            'slug'            => $this->getSlug(),
            'current_version' => $version,
            'file'            => $file,
        ];
    }
}
