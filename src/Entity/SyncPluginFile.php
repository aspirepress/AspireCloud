<?php

namespace App\Entity;

use App\Repository\SyncPluginFileRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;

#[ORM\Entity(repositoryClass: SyncPluginFileRepository::class)]
#[ORM\Table(name: 'sync_plugin_files')]
class SyncPluginFile
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SyncPlugin $plugin = null;

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
}
