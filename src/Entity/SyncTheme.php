<?php

namespace App\Entity;

use App\Repository\SyncThemeRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SyncThemeRepository::class)]
class SyncTheme
{
    //region properties

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private Uuid $id;

    #[ORM\Column(length: 1024)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $slug;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $current_version = null;

    #[ORM\Column]
    private DateTimeImmutable $updated;

    #[ORM\Column]
    private DateTimeImmutable $pulled_at;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $metadata = null;

    /**
     * @var Collection<int, SyncThemeFile>
     */
    #[ORM\OneToMany(targetEntity: SyncThemeFile::class, mappedBy: 'theme', orphanRemoval: true)]
    private Collection $files;

    //endregion

    //region getters and setters

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): SyncTheme
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): SyncTheme
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCurrentVersion(): ?string
    {
        return $this->current_version;
    }

    public function setCurrentVersion(?string $current_version): SyncTheme
    {
        $this->current_version = $current_version;
        return $this;
    }

    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdated(DateTimeImmutable $updated): SyncTheme
    {
        $this->updated = $updated;
        return $this;
    }

    public function getPulledAt(): DateTimeImmutable
    {
        return $this->pulled_at;
    }

    public function setPulledAt(DateTimeImmutable $pulled_at): SyncTheme
    {
        $this->pulled_at = $pulled_at;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): SyncTheme
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return Collection<int, SyncThemeFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(SyncThemeFile $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setTheme($this);
        }
        return $this;
    }

    public function removeFile(SyncThemeFile $file): static
    {
        // set the owning side to null (unless already changed)
        if ($this->files->removeElement($file) && $file->getTheme() === $this) {
            $file->setTheme(null);
        }
        return $this;
    }

    //endregion
}
