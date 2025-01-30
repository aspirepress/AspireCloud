<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('plugin_files', 'sync_plugin_files');
        Schema::rename('plugins', 'sync_plugins');
        Schema::rename('revisions', 'sync_revisions');
        Schema::rename('sites', 'sync_sites');
        Schema::rename('stats', 'sync_stats');
        Schema::rename('theme_files', 'sync_theme_files');
        Schema::rename('themes', 'sync_themes');
        Schema::rename('not_found_items', 'sync_not_found_items');
    }

    public function down(): void
    {
        Schema::rename('sync_plugin_files', 'plugin_files');
        Schema::rename('sync_plugins', 'plugins');
        Schema::rename('sync_revisions', 'revisions');
        Schema::rename('sync_sites', 'sites');
        Schema::rename('sync_stats', 'stats');
        Schema::rename('sync_theme_files', 'theme_files');
        Schema::rename('sync_themes', 'themes');
        Schema::rename('sync_not_found_items', 'not_found_items');
    }
};
