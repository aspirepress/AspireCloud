<?php

namespace App\Http\Resources\Plugins;

use App\Models\WpOrg\Plugin;
use Illuminate\Http\Request;

class PluginResource extends BasePluginResource
{
    public const LAST_UPDATED_DATE_FORMAT = 'Y-m-d H:ia T'; // .org's goofy format: "2024-09-27 9:53pm GMT"

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $plugin = $this->resource;
        assert($plugin instanceof Plugin);

        $data = array_merge($this->getCommonAttributes(), [
            'author' => $plugin->author,
            'author_profile' => $plugin->author_profile,
            'rating' => $plugin->rating,
            'num_ratings' => $plugin->num_ratings,
            'ratings' => $this->mapRatings($plugin->getRatings()),
            'support_threads' => $plugin->support_threads,
            'support_threads_resolved' => $plugin->support_threads_resolved,
            'active_installs' => $plugin->active_installs,
            'last_updated' => $plugin->last_updated?->format(self::LAST_UPDATED_DATE_FORMAT),
            'added' => $plugin->added->format('Y-m-d'),
            'homepage' => $plugin->homepage,
            'tags' => $plugin->tagsArray(),
            'donate_link' => $plugin->donate_link,
            'requires_plugins' => $plugin->getRequiresPlugins(),
        ]);

        return match ($request->query('action')) {
            'query_plugins' => array_merge($data, [
                'downloaded' => $plugin->downloaded,
                'short_description' => $plugin->short_description,
                'description' => $plugin->description,
                'icons' => $plugin->getIcons(),
            ]),
            'plugin_information' => array_merge($data, [
                'sections' => $plugin->getSections(),
                'versions' => $plugin->getVersions(),
                'contributors' => $plugin->getContributors(),
                'screenshots' => $plugin->getScreenshots(),
                'support_url' => $plugin->support_url,
                'upgrade_notice' => $plugin->getUpgradeNotice(),
                'business_model' => $plugin->business_model,
                'repository_url' => $plugin->repository_url,
                'commercial_support_url' => $plugin->commercial_support_url,
                'banners' => $plugin->getBanners(),
                'preview_link' => $plugin->preview_link,
            ]),
            default => $data,
        };
    }
}
