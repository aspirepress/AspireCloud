<?php

namespace App\Entity;

use App\Repository\SyncPluginRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SyncPluginRepository::class)]
#[ORM\Table(name: 'sync_plugins')]
class SyncPlugin
{
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

    #[Orm\Column]
    private string $status; // TODO make this an enum

    #[ORM\Column]
    private DateTimeImmutable $pulled_at;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $metadata = null;

    /**
     * @var Collection<int, SyncPluginFile>
     */
    #[ORM\OneToMany(targetEntity: SyncPluginFile::class, mappedBy: 'plugin', orphanRemoval: true)]
    private Collection $files;

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

    public function setName(string $name): SyncPlugin
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): SyncPlugin
    {
        $this->slug = $slug;
        return $this;
    }

    public function getCurrentVersion(): ?string
    {
        return $this->current_version;
    }

    public function setCurrentVersion(?string $current_version): SyncPlugin
    {
        $this->current_version = $current_version;
        return $this;
    }

    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdated(DateTimeImmutable $updated): SyncPlugin
    {
        $this->updated = $updated;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): SyncPlugin
    {
        $this->status = $status;
        return $this;
    }

    public function getPulledAt(): DateTimeImmutable
    {
        return $this->pulled_at;
    }

    public function setPulledAt(DateTimeImmutable $pulled_at): SyncPlugin
    {
        $this->pulled_at = $pulled_at;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): SyncPlugin
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * @return Collection<int, SyncPluginFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(SyncPluginFile $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setPlugin($this);
        }

        return $this;
    }

    public function removeFile(SyncPluginFile $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getPlugin() === $this) {
                $file->setPlugin(null);
            }
        }

        return $this;
    }
}
