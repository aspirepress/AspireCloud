<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WpOrg\Plugin;
use Elastic\Elasticsearch\Client;

class ReindexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:plugins {--chunk=500 : Number of models to process per chunk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex all plugins into Elasticsearch';

    /**
     * Execute the console command.
     */
    public function handle(Client $client): int
    {
        $chunkSize = (int)$this->option('chunk');

        $this->info("Reindexing plugins in chunks of {$chunkSize}...");

        Plugin::query()
            ->with('tags', 'contributors')
            ->chunk($chunkSize, function ($plugins) use ($client) {
            foreach ($plugins as $plugin) {
                $client->index(
                    [
                        'index' => 'plugins',
                        'id' => $plugin->id,
                        'body' => $plugin->toSearchArray(),
                    ]
                );
                $this->line("Indexed plugin #{$plugin->id}");
            }
        });

        $this->info('Reindexing complete.');

        return self::SUCCESS;
    }
}
