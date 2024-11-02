<?php

namespace App\Http\Resources\Plugins;

use App\Models\WpOrg\Plugin;
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
        $plugin = $this->resource;
        assert($plugin instanceof Plugin);

        return [
            'name' => $plugin->name,
            'slug' => $plugin->slug,
            'version' => $plugin->version,
            'requires' => $plugin->requires,
            'tested' => $plugin->tested,
            'requires_php' => $plugin->requires_php,
            'download_link' => $plugin->download_link,
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
