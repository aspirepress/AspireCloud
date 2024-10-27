<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sync\SyncPlugin;
use App\Models\WpOrg\Plugin;

class PopulatePluginsCommand extends Command
{
    protected $signature = 'db:populate:plugins';

    protected $description = 'Loads plugins from AspireSync metadata';

    public function handle(): void
    {
        SyncPlugin::query()
            ->where('status', 'open')
            ->cursor()
            ->each(function (SyncPlugin $sync) {
                $this->info("$sync->slug ...");
                Plugin::getOrCreateFromSyncPlugin($sync);
            });
    }
}
