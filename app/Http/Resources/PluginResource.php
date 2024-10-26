<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class PluginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'version' => $this->resource->version,
            'author' => $this->resource->author,
            'author_profile' => $this->resource->author_profile,
            'requires' => $this->resource->requires,
            'tested' => $this->resource->tested,
            'requires_php' => $this->resource->requires_php,
            'rating' => $this->resource->rating,
            'num_ratings' => $this->resource->num_ratings,
            'ratings' => $this->mapRatings($this->resource->ratings),
            'support_threads' => $this->resource->support_threads,
            'support_threads_resolved' => $this->resource->support_threads_resolved,
            'active_installs' => $this->resource->active_installs,
            'downloaded' => $this->resource->downloaded,
            'last_updated' => $this->resource->last_updated?->format('Y-m-d H:i:s'),
            'added' => $this->resource->added?->format('Y-m-d'),
            'homepage' => $this->resource->homepage,
            'download_link' => $this->resource->download_link,
            'tags' => $this->resource->tags,
            'donate_link' => $this->resource->donate_link,
        ];

        // Add fields specific to the plugin list
        if ($request->query('action') === 'query_plugins') {
            $data['short_description'] = $this->resource->short_description;
            $data['description'] = $this->resource->description;
            $data['icons'] = $this->resource->icons;
            $data['requires_plugins'] = $this->resource->requires_plugins ?? [];
        }

        // Add fields specific to single plugin
        if ($request->query('action') === 'plugin_information') {
            $data['sections'] = $this->resource->sections;
            $data['versions'] = $this->resource->versions;
            $data['contributors'] = $this->resource->contributors;
            $data['screenshots'] = $this->resource->screenshots;
        }

        return $data;
    }

    /**
     * @param array<int> $ratings
     * @return Collection<string, int>
      */
    private function mapRatings(array $ratings): Collection
    {
        return collect($ratings)
            ->mapWithKeys(fn($value, $key) => [(string) $key => $value]);
    }
}
