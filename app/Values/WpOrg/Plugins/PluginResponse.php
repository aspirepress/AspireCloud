<?php

declare(strict_types=1);

namespace App\Values\WpOrg\Plugins;

use App\Models\WpOrg\Plugin;
use App\Values\DTO;
use App\Values\WpOrg\Author;
use Bag\Attributes\Transforms;
use Bag\Values\Optional;
use DateTimeInterface;

readonly class PluginResponse extends DTO
{
    public const LAST_UPDATED_DATE_FORMAT = 'Y-m-d h:ia T'; // .org's goofy format: "2024-09-27 9:53pm GMT"

    /**
     * @param array<array-key, mixed> $banners
     * @param array<array-key, array{src: string, caption: string}> $screenshots
     * @param array<string, Author> $contributors
     * @param array<string, string> $versions
     * @param array<string, string> $sections
     * @param array{"1":int, "2":int, "3":int, "4":int, "5":int} $ratings
     * @param list<string> $requires_plugins
     * @param array<string, string> $icons
     * @param array<string, string> $upgrade_notice
     * @param array<string, string> $tags
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $version,
        public string|null $requires,
        public string|null $tested,
        public string|null $requires_php,
        public string $download_link,
        public string $author,
        public string|null $author_profile,
        public int $rating,
        public int $num_ratings,
        public array $ratings,
        public int $support_threads,
        public int $support_threads_resolved,
        public int $active_installs,
        public string|null $last_updated,
        public string|null $added,
        public string|null $homepage,
        public array $tags,
        public string|null $donate_link,
        public array $requires_plugins,

        // query_plugins only
        public Optional|string|null $downloaded,
        public Optional|string|null $short_description,
        public Optional|string|null $description,
        public Optional|array $icons,

        // plugin_information only
        public Optional|array $sections,
        public Optional|array $versions,
        public Optional|array $contributors,
        public Optional|array $screenshots,
        public Optional|string|null $support_url,
        public Optional|array|null $upgrade_notice,
        public Optional|string|null $business_model,
        public Optional|string|null $repository_url,
        public Optional|string|null $commercial_support_url,
        public Optional|array $banners,
        public Optional|string|null $preview_link,

        // aspirecloud metadata
        public Optional|string $ac_origin,
        public Optional|DateTimeInterface $ac_created,
    ) {}

    /** @return array<string, mixed> */
    #[Transforms(Plugin::class)]
    public static function fromPlugin(Plugin $plugin): array
    {
        $none = new Optional();

        return [
            // common
            'name' => $plugin->name,
            'slug' => $plugin->slug,
            'version' => $plugin->version,
            'requires' => $plugin->requires,
            'tested' => $plugin->tested,
            'requires_php' => $plugin->requires_php,
            'download_link' => $plugin->download_link,
            'author' => $plugin->author,
            'author_profile' => $plugin->author_profile,
            'rating' => $plugin->rating,
            'num_ratings' => $plugin->num_ratings,
            'ratings' => $plugin->ratings,
            'support_threads' => $plugin->support_threads,
            'support_threads_resolved' => $plugin->support_threads_resolved,
            'active_installs' => $plugin->active_installs,
            'last_updated' => self::formatLastUpdated($plugin->last_updated),
            'added' => $plugin->added?->format('Y-m-d'),
            'homepage' => $plugin->homepage,
            'tags' => $plugin->tagsArray(),
            'donate_link' => $plugin->donate_link,
            'requires_plugins' => $plugin->requires_plugins,

            // (formerly) query_plugins only
            'downloaded' => $plugin->downloaded,
            'short_description' => $plugin->short_description,
            'description' => $plugin->description,
            'icons' => $plugin->icons,

            // (formerly) plugin_information only
            'sections' => $plugin->sections,
            'versions' => $plugin->versions,
            'contributors' => $plugin->contributors->mapWithKeys(
                fn($authorModel) => [$authorModel->user_nicename => Author::from($authorModel)],
            )->toArray(),
            'screenshots' => $plugin->screenshots,
            'support_url' => $plugin->support_url,
            'upgrade_notice' => $plugin->upgrade_notice ?: $none,
            'business_model' => $plugin->business_model,
            'repository_url' => $plugin->repository_url,
            'commercial_support_url' => $plugin->commercial_support_url,
            'banners' => $plugin->banners,
            'preview_link' => $plugin->preview_link,

            // aspirecloud metadata
            'ac_origin' => $plugin->ac_origin,
            'ac_created' => $plugin->ac_created,
        ];
    }

    private static function formatLastUpdated(?DateTimeInterface $lastUpdated): ?string
    {
        if ($lastUpdated === null) {
            return null;
        }
        $out = $lastUpdated->format(self::LAST_UPDATED_DATE_FORMAT);
        // Unfortunately this seems to render GMT as "GMT+0000" for some reason, so strip that out
        return \Safe\preg_replace('/\+\d+$/', '', $out);
    }
}
