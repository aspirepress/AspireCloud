<?php

namespace App\Http\Resources\Plugins;

use Illuminate\Http\Request;

class PluginUpdateResource extends BasePluginResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => "w.org/plugins/{$this->resource['slug']}",
            'slug' => $this->resource['slug'],
            'plugin' => $this->resource['plugin'],
            'new_version' => $this->resource['new_version'],
            'url' => "https://wordpress.org/plugins/{$this->resource['slug']}/",
            'package' => $this->resource['package'],
            'icons' => $this->resource['icons'],
            'banners' => $this->resource['banners'],
            'banners_rtl' => $this->resource['banners_rtl'] ?? [],
            'requires' => $this->resource['requires'],
            'tested' => $this->resource['tested'],
            'requires_php' => $this->resource['requires_php'],
            'requires_plugins' => $this->resource['requires_plugins'] ?? [],
            'compatibility' => $this->resource['compatibility'] ?? [],
        ];
    }
}
