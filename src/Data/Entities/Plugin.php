<?php

declare(strict_types=1);

namespace AspirePress\Cdn\Data\Entities;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

final class Plugin
{
    private function __construct(
        private UuidInterface $id,
        private string $name,
        private string $slug,
        private string $currentVersion,
        private DownloadableFile $file
    )
    {
    }

    public static function fromArray(array $data): self
    {
        Assert::keyExists($data, 'id');
        Assert::keyExists($data, 'name');
        Assert::keyExists($data, 'slug');
        Assert::keyExists($data, 'current_version');
        Assert::keyExists($data, 'file');

        Assert::uuid($data['id']);
        Assert::string($data['name']);
        Assert::string($data['slug']);
        Assert::string($data['current_version']);
        Assert::isInstanceOf($data['file'], DownloadableFile::class);

        return new self (
            Uuid::fromString($data['id']),
            $data['name'],
            $data['slug'],
            $data['current_version'],
            $data['file']
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

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function getFile(): DownloadableFile
    {
        return $this->file;
    }

    public function newerVersionAvailable($testVersion)
    {
        $currentVersion = $this->getCurrentVersion();

        $cvParts = explode('.', $currentVersion);
        $tvParts = explode('.', $testVersion);

        if ($cvParts[0] > $tvParts[0]) {
            return true;
        }


    }
}
