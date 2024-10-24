<?php

namespace App\Entity;

use App\Repository\SyncThemeFileRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;

#[ORM\Entity(repositoryClass: SyncThemeFileRepository::class)]
#[ORM\Table(name: 'sync_theme_files')]
class SyncThemeFile
{
    //region properties

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SyncTheme $theme = null;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $file_url;

    #[ORM\Column(length: 32)]
    private string $type;

    #[ORM\Column(length: 32)]
    private string $version;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $metadata = null;

    #[ORM\Column]
    private DateTimeImmutable $created;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $processed = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hash = null;

    //endregion

    //region getters and setters

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): SyncThemeFile
    {
        $this->id = $id;
        return $this;
    }

    public function getTheme(): ?SyncTheme
    {
        return $this->theme;
    }

    public function setTheme(?SyncTheme $theme): SyncThemeFile
    {
        $this->theme = $theme;
        return $this;
    }

    public function getFileUrl(): ?string
    {
        return $this->file_url;
    }

    public function setFileUrl(?string $file_url): SyncThemeFile
    {
        $this->file_url = $file_url;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): SyncThemeFile
    {
        $this->type = $type;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): SyncThemeFile
    {
        $this->version = $version;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): SyncThemeFile
    {
        $this->metadata = $metadata;
        return $this;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(DateTimeImmutable $created): SyncThemeFile
    {
        $this->created = $created;
        return $this;
    }

    public function getProcessed(): ?DateTimeImmutable
    {
        return $this->processed;
    }

    public function setProcessed(?DateTimeImmutable $processed): SyncThemeFile
    {
        $this->processed = $processed;
        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): SyncThemeFile
    {
        $this->hash = $hash;
        return $this;
    }

    //endregion
}

