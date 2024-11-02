<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\Plugin;

class PluginInformationService
{
    public function findBySlug(string $slug): ?Plugin
    {
        return Plugin::query()->where('slug', $slug)->first();
    }
}
