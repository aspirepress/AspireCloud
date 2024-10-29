<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sync\SyncTheme;
use App\Models\WpOrg\Theme;

class PopulateThemesCommand extends Command
{
    protected $signature = 'db:populate:themes';

    protected $description = 'Loads themes from AspireSync metadata';

    public function handle(): void
    {
        SyncTheme::cursor()->each(function (SyncTheme $sync) {
            $this->info("$sync->slug ...");
            Theme::getOrCreateFromSyncTheme($sync);
        });
    }
}
