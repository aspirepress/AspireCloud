<?php

namespace App\Entity;

use App\Repository\PluginRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PluginRepository::class)]
#[Orm\Table(name: 'plugins')]
class Plugin
{
    //region properties

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne]
    private ?SyncPlugin $sync_plugin = null;

    #[ORM\Column]
    private string $slug;

    #[ORM\Column]
    private string $name;

    #[ORM\Column]
    private string $short_description;

    #[ORM\Column]
    private string $description;

    #[ORM\Column]
    private string $version;

    #[ORM\Column]
    private string $author;

    #[ORM\Column]
    private string $requires;

    #[ORM\Column]
    private string $requires_php;

    #[ORM\Column]
    private string $tested;

    #[ORM\Column]
    private string $download_link;

    #[ORM\Column]
    private \DateTimeImmutable $added;

    #[ORM\Column]
    private \DateTimeImmutable $last_updated;

    #[ORM\Column]
    private ?string $author_profile;

    #[ORM\Column]
    private int $rating = 0;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $ratings = null;

    #[ORM\Column]
    private int $num_ratings = 0;

    #[ORM\Column]
    private int $support_threads = 0;

    #[ORM\Column]
    private int $support_threads_resolved = 0;

    #[ORM\Column]
    private int $active_installs = 0;

    #[ORM\Column]
    private int $downloaded = 0;

    #[ORM\Column]
    private ?string $homepage = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $banners = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $tags = null;

    #[ORM\Column]
    private ?string $donate_link = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $contributors = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $icons = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $source = null;

    #[ORM\Column]
    private ?string $business_model = null;

    #[ORM\Column]
    private ?string $commercial_support_url = null;

    #[ORM\Column]
    private ?string $support_url = null;

    #[ORM\Column]
    private ?string $preview_link = null;

    #[ORM\Column]
    private ?string $repository_url = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $requires_plugins = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $compatibility = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $screenshots = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $sections = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $versions = null;

    #[ORM\Column(type: Types::JSON, nullable: true, options: ['jsonb' => true])]
    private ?array $upgrade_notice = null;

    //endregion

    //region getters and setters

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSyncPlugin(): ?SyncPlugin
    {
        return $this->sync_plugin;
    }

    public function setSyncPlugin(?SyncPlugin $sync_plugin): static
    {
        $this->sync_plugin = $sync_plugin;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): Plugin
    {
        $this->slug = $slug;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Plugin
    {
        $this->name = $name;
        return $this;
    }

    public function getShortDescription(): string
    {
        return $this->short_description;
    }

    public function setShortDescription(string $short_description): Plugin
    {
        $this->short_description = $short_description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Plugin
    {
        $this->description = $description;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): Plugin
    {
        $this->version = $version;
        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): Plugin
    {
        $this->author = $author;
        return $this;
    }

    public function getRequires(): string
    {
        return $this->requires;
    }

    public function setRequires(string $requires): Plugin
    {
        $this->requires = $requires;
        return $this;
    }

    public function getRequiresPhp(): string
    {
        return $this->requires_php;
    }

    public function setRequiresPhp(string $requires_php): Plugin
    {
        $this->requires_php = $requires_php;
        return $this;
    }

    public function getTested(): string
    {
        return $this->tested;
    }

    public function setTested(string $tested): Plugin
    {
        $this->tested = $tested;
        return $this;
    }

    public function getDownloadLink(): string
    {
        return $this->download_link;
    }

    public function setDownloadLink(string $download_link): Plugin
    {
        $this->download_link = $download_link;
        return $this;
    }

    public function getAdded(): \DateTimeImmutable
    {
        return $this->added;
    }

    public function setAdded(\DateTimeImmutable $added): Plugin
    {
        $this->added = $added;
        return $this;
    }

    public function getLastUpdated(): \DateTimeImmutable
    {
        return $this->last_updated;
    }

    public function setLastUpdated(\DateTimeImmutable $last_updated): Plugin
    {
        $this->last_updated = $last_updated;
        return $this;
    }

    public function getAuthorProfile(): ?string
    {
        return $this->author_profile;
    }

    public function setAuthorProfile(?string $author_profile): Plugin
    {
        $this->author_profile = $author_profile;
        return $this;
    }

    public function getRating(): int
    {
        return $this->rating;
    }

    public function setRating(int $rating): Plugin
    {
        $this->rating = $rating;
        return $this;
    }

    public function getRatings(): ?array
    {
        return $this->ratings;
    }

    public function setRatings(?array $ratings): Plugin
    {
        $this->ratings = $ratings;
        return $this;
    }

    public function getNumRatings(): int
    {
        return $this->num_ratings;
    }

    public function setNumRatings(int $num_ratings): Plugin
    {
        $this->num_ratings = $num_ratings;
        return $this;
    }

    public function getSupportThreads(): int
    {
        return $this->support_threads;
    }

    public function setSupportThreads(int $support_threads): Plugin
    {
        $this->support_threads = $support_threads;
        return $this;
    }

    public function getSupportThreadsResolved(): int
    {
        return $this->support_threads_resolved;
    }

    public function setSupportThreadsResolved(int $support_threads_resolved): Plugin
    {
        $this->support_threads_resolved = $support_threads_resolved;
        return $this;
    }

    public function getActiveInstalls(): int
    {
        return $this->active_installs;
    }

    public function setActiveInstalls(int $active_installs): Plugin
    {
        $this->active_installs = $active_installs;
        return $this;
    }

    public function getDownloaded(): int
    {
        return $this->downloaded;
    }

    public function setDownloaded(int $downloaded): Plugin
    {
        $this->downloaded = $downloaded;
        return $this;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage): Plugin
    {
        $this->homepage = $homepage;
        return $this;
    }

    public function getBanners(): ?array
    {
        return $this->banners;
    }

    public function setBanners(?array $banners): Plugin
    {
        $this->banners = $banners;
        return $this;
    }

    public function getTags(): ?array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): Plugin
    {
        $this->tags = $tags;
        return $this;
    }

    public function getDonateLink(): ?string
    {
        return $this->donate_link;
    }

    public function setDonateLink(?string $donate_link): Plugin
    {
        $this->donate_link = $donate_link;
        return $this;
    }

    public function getContributors(): ?array
    {
        return $this->contributors;
    }

    public function setContributors(?array $contributors): Plugin
    {
        $this->contributors = $contributors;
        return $this;
    }

    public function getIcons(): ?array
    {
        return $this->icons;
    }

    public function setIcons(?array $icons): Plugin
    {
        $this->icons = $icons;
        return $this;
    }

    public function getSource(): ?array
    {
        return $this->source;
    }

    public function setSource(?array $source): Plugin
    {
        $this->source = $source;
        return $this;
    }

    public function getBusinessModel(): ?string
    {
        return $this->business_model;
    }

    public function setBusinessModel(?string $business_model): Plugin
    {
        $this->business_model = $business_model;
        return $this;
    }

    public function getCommercialSupportUrl(): ?string
    {
        return $this->commercial_support_url;
    }

    public function setCommercialSupportUrl(?string $commercial_support_url): Plugin
    {
        $this->commercial_support_url = $commercial_support_url;
        return $this;
    }

    public function getSupportUrl(): ?string
    {
        return $this->support_url;
    }

    public function setSupportUrl(?string $support_url): Plugin
    {
        $this->support_url = $support_url;
        return $this;
    }

    public function getPreviewLink(): ?string
    {
        return $this->preview_link;
    }

    public function setPreviewLink(?string $preview_link): Plugin
    {
        $this->preview_link = $preview_link;
        return $this;
    }

    public function getRepositoryUrl(): ?string
    {
        return $this->repository_url;
    }

    public function setRepositoryUrl(?string $repository_url): Plugin
    {
        $this->repository_url = $repository_url;
        return $this;
    }

    public function getRequiresPlugins(): ?array
    {
        return $this->requires_plugins;
    }

    public function setRequiresPlugins(?array $requires_plugins): Plugin
    {
        $this->requires_plugins = $requires_plugins;
        return $this;
    }

    public function getCompatibility(): ?array
    {
        return $this->compatibility;
    }

    public function setCompatibility(?array $compatibility): Plugin
    {
        $this->compatibility = $compatibility;
        return $this;
    }

    public function getScreenshots(): ?array
    {
        return $this->screenshots;
    }

    public function setScreenshots(?array $screenshots): Plugin
    {
        $this->screenshots = $screenshots;
        return $this;
    }

    public function getSections(): ?array
    {
        return $this->sections;
    }

    public function setSections(?array $sections): Plugin
    {
        $this->sections = $sections;
        return $this;
    }

    public function getVersions(): ?array
    {
        return $this->versions;
    }

    public function setVersions(?array $versions): Plugin
    {
        $this->versions = $versions;
        return $this;
    }

    public function getUpgradeNotice(): ?array
    {
        return $this->upgrade_notice;
    }

    public function setUpgradeNotice(?array $upgrade_notice): Plugin
    {
        $this->upgrade_notice = $upgrade_notice;
        return $this;
    }

    //endregion
}

