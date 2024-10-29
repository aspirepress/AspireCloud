<?php

namespace App\Console\Commands;

use App\Models\Sync\SyncTheme;
use App\Models\WpOrg\Theme;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateThemesCommand extends Command
{
    protected $signature = 'db:populate:themes {--days=} {--create-only} {--delete}';

    protected $description = 'Loads themes from AspireSync metadata';

    public function handle(): void
    {
        $create_only = $this->option('create-only');
        $days = (int) $this->option('days');

        if ($this->option('delete')) {
            $query = DB::table('themes')->whereNotNull('sync_id');
            $days and $query->whereDate('pulled_at', '>=', now()->subDays($days));
            $query->delete();
        }

        $query = SyncTheme::query();
        $days and $query->whereDate('pulled_at', '>=', now()->subDays($days));

        foreach ($query->cursor() as $sync) {
            $this->info("$sync->slug ...");
            $theme = Theme::getOrCreateFromSyncTheme($sync);
            if (!$create_only) {
                $theme->updateFromSyncTheme();
            }
        }
    }
}
