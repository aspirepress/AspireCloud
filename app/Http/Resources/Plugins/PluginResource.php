<?php

namespace App\Http\Resources\Plugins;

use Illuminate\Http\Request;

class PluginResource extends BasePluginResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = array_merge($this->getCommonAttributes(), [
            'author' => $this->resource->author,
            'author_profile' => $this->resource->author_profile,
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
            'tags' => $this->resource->tags,
            'donate_link' => $this->resource->donate_link,
        ]);

        return match ($request->query('action')) {
            'query_plugins' => array_merge($data, [
                'short_description' => $this->resource->short_description,
                'description' => $this->resource->description,
                'icons' => $this->resource->icons,
                'requires_plugins' => $this->resource->requires_plugins ?? [],
            ]),
            'plugin_information' => array_merge($data, [
                'sections' => $this->resource->sections,
                'versions' => $this->resource->versions,
                'contributors' => $this->resource->contributors,
                'screenshots' => $this->resource->screenshots,
            ]),
            default => $data,
        };
    }
}
