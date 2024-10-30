<?php

namespace App\Http\Resources\Plugins;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

abstract class BasePluginResource extends JsonResource
{
    /**
     * Get common plugin attributes that are shared across different contexts
     *
     * @return array<string, mixed>
     */
    protected function getCommonAttributes(): array
    {
        return [
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'version' => $this->resource->version,
            'requires' => $this->resource->requires,
            'tested' => $this->resource->tested,
            'requires_php' => $this->resource->requires_php,
            'download_link' => $this->resource->download_link,
        ];
    }

    /**
     * @param array<int> $ratings
     * @return Collection<string, int>
     */
    protected function mapRatings(array $ratings): Collection
    {
        return collect($ratings)
            ->mapWithKeys(fn($value, $key) => [(string) $key => $value]);
    }
}
