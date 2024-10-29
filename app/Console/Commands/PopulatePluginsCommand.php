<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sync\SyncPlugin;
use App\Models\WpOrg\Plugin;
use Illuminate\Support\Facades\DB;

class PopulatePluginsCommand extends Command
{
    protected $signature = 'db:populate:plugins {--create-only} {--days=}';

    protected $description = 'Loads plugins from AspireSync metadata';

    public function handle(): void
    {
        $create_only = $this->option('create-only');
        $days = (int)$this->option('days');

        if ($this->option('delete')) {
            $query = DB::table('themes')->whereNotNull('sync_id');
            $days and $query->whereDate('pulled_at', '>=', now()->subDays($days));
            $query->delete();
        }

        $query = SyncPlugin::query()->where('status', 'open');
        $days and $query->whereDate('pulled_at', '>=', now()->subDays($days));

        foreach ($query->cursor() as $sync) {
            $this->info("$sync->slug ...");
            $plugin = Plugin::getOrCreateFromSyncPlugin($sync);
            if (!$create_only) {
                $plugin->updateFromSyncPlugin();
            }
        }
    }
}
