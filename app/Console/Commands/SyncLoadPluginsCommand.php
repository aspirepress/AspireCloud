<?php

namespace App\Console\Commands;

use App\Models\Sync\SyncPlugin;
use App\Models\WpOrg\Plugin;
use App\Utils\File;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SyncLoadPluginsCommand extends Command
{
    protected $signature = 'sync:load:plugins {file} {--validate-only} {--new-only}';

    protected $description = 'Loads plugins from AspireSync metadata';

    private int $currentLine = 0;

    public function handle(): void
    {
        $filename = $this->argument('file');
        if (in_array($filename, ['-', '/dev/stdin', 'php://stdin'])) {
            $filename = 'php://stdin';
        } elseif (!file_exists($filename)) {
            $this->fail("$filename: file not found");
        }

        $validateOnly = $this->option('validate-only');

        $imported = 0;
        $errors = 0;

        foreach (File::lazyLines($filename) as $line) {
            $this->currentLine++;
            $line = trim($line);
            if (!$line) {
                continue;
            }
            try {
                $metadata = \Safe\json_decode($line, true);
            } catch (Exception $e) {
                $errors++;
                $this->error("Line $this->currentLine: error reading metadata: {$e->getMessage()}");
                echo "Partial line: " . Str::substr($line, 0, 100) . "\n";
                continue;
            }
            if ($validateOnly) {
                continue;
            }
            try {
                $slug = $metadata['slug'];
                $result = $this->loadMetadata($metadata);
                $message = $result['message'];
                $this->info("$slug ... $message");
                $imported++;
            } catch (Exception $e) {
                $errors++;
                $this->error("Line $this->currentLine: error importing plugin: {$e->getMessage()}");
                echo "Partial line: " . Str::substr($line, 0, 100) . "\n";
            }
        }

        if ($errors > 0) {
            $this->fail("Imported $imported items; $errors errors");
        }

        $this->info("Imported $imported items.");
    }

    /**
     * @param array<string, mixed> $metadata
     * @return array{message: string, sync: SyncPlugin|null, plugin: Plugin|null}
     */
    private function loadMetadata(array $metadata): array
    {
        $newOnly = $this->option('new-only');

        $sync = null;
        $plugin = null;

        $withMessage = fn(string $message) => ['message' => $message, 'sync' => $sync, 'plugin' => $plugin];
        if ($newOnly) {
            $sync = SyncPlugin::firstWhere('slug', $metadata['slug']);
            if ($sync) {
                return $withMessage("skipping existing plugin");
            }
        }

        // dummy variable because phpstorm freaks out about "immediate reassignment" otherwise.
        $_sync = SyncPlugin::updateOrCreate(
            ['slug' => $metadata['slug']],
            [
                'name' => Str::substr($metadata['name'], 0, 255),
                'current_version' => $metadata['version'],
                'status' => 'open',
                'metadata' => $metadata,
            ],
        );
        $sync = $_sync;

        $plugin = Plugin::getOrCreateFromSyncPlugin($sync);
        $plugin->updateFromSyncPlugin();  // redundant if newly created.  oh well.
        return $withMessage("imported");
    }
}
