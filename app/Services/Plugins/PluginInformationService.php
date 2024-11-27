<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;

class PluginInformationService
{
    public function findBySlug(string $slug): Plugin|ClosedPlugin|null
    {
        return
            ClosedPlugin::query()->where('slug', $slug)->first()
            ?? Plugin::query()->where('slug', $slug)->first();
    }
}
