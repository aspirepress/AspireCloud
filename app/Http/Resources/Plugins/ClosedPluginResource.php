<?php

namespace App\Http\Resources\Plugins;

use App\Models\WpOrg\ClosedPlugin;
use Illuminate\Http\Request;

class ClosedPluginResource extends BasePluginResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $plugin = $this->resource;
        assert($plugin instanceof ClosedPlugin);

        return [
            'error' => 'closed',
            'name' => $plugin->name,
            'slug' => $plugin->slug,
            'description' => $plugin->description,
            'closed' => true,
            'closed_date' => $plugin->closed_date->format('Y-m-d'),
            'reason' => $plugin->reason,
            'reason_text' => $plugin->getReasonText(),
        ];
    }
}
