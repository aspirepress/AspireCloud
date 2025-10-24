<?php

namespace App\Console\Commands;

use App\Models\WpOrg\Plugin;
use App\Services\Elastic\IndexService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexPluginsCommand extends Command
{
    protected $signature = 'elastic:plugins {--chunk=500 : Number of models to process per chunk}';

    protected $description = 'Reindex all plugins into Elasticsearch';

    public function __construct(private readonly IndexService $index) {
        parent::__construct();
    }

    public function handle(): int
    {
        $chunkSize = (int)$this->option('chunk') ?: 500;

        $this->info("Reindexing plugins in chunks of {$chunkSize}...");

        Plugin::query()
            ->with('tags', 'contributors')
            ->chunk($chunkSize, fn (Collection $plugins) => $plugins->each($this->indexPlugin(...)));

        $this->info('Reindexing complete.');

        return self::SUCCESS;
    }

    private function indexPlugin(Plugin $plugin): void
    {
        $this->index->add($plugin);
        $this->line("Indexed plugin #{$plugin->id}");
    }
}
