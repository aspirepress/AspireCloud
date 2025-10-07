<?php

namespace App\Console\Commands;

use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use App\Utils\File;
use App\Utils\Regex;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Safe\ini_set;
use function Safe\json_decode;
use function Safe\preg_replace;

class SyncLoadCommand extends Command
{
    protected $signature = 'sync:load {file} {--validate-only} {--new-only} {--stop-on-first-error}';

    protected $description = 'Loads plugins and themes from AspireSync metadata';

    private int $currentLine = 0;

    private int $errors = 0;

    private int $loaded = 0;

    public function handle(Pipeline $pipeline): void
    {
        ini_set('memory_limit', '-1');

        $filename = (string)$this->argument('file');
        if (in_array($filename, ['-', '/dev/stdin', 'php://stdin'])) {
            $filename = 'php://stdin';
        } elseif (!file_exists($filename)) {
            $this->fail("$filename: file not found");
        }

        $stages = [
            $this->decodeLine(...),
            $this->decorateWithClass(...),
            $this->loadOne(...),
        ];

        foreach (File::lazyLines($filename) as $line) {
            $this->currentLine++;
            try {
                DB::transaction(fn() => $pipeline->send($line)->through($stages)->thenReturn());
            } catch (Exception $e) {
                $this->errors++;
                $this->error("Line $this->currentLine: {$e->getMessage()}");
                echo "Partial line: " . Str::substr($line, 0, 100) . "\n";
                $this->option('stop-on-first-error') and $this->fail("Errors encountered -- aborting.");
            }
        }

        if ($this->errors > 0) {
            $this->fail("loaded $this->loaded items; $this->errors errors");
        }

        $this->info("loaded $this->loaded items.");
    }

    private function decodeLine(string $line, Closure $next): void
    {
        $line = trim($line);
        if (!$line) {
            return;
        }
        $metadata = json_decode($line, true);
        if ($this->option('validate-only')) {
            return;
        }
        $next($metadata);
    }

    /** @param array<string, mixed> $metadata */
    private function decorateWithClass(array $metadata, Closure $next): void
    {
        $sync_meta = $metadata['aspiresync_meta'];
        $type = $sync_meta['type'];
        $status = $sync_meta['status'];

        $class = match ($type) {
            'plugin' => match ($status) {
                'open' => Plugin::class,
                'closed' => ClosedPlugin::class,
                default => throw new Exception("Unknown plugin status: {$status}"),
            },
            'theme' => match ($status) {
                'open' => Theme::class,
                // Closed themes don't seem to be a thing, they're just 404 in the API
                default => throw new Exception("Unknown theme status: {$status}"),
            },
            default => throw new Exception("Unknown plugin type: {$type}"),
        };
        $next(['class' => $class, 'metadata' => $metadata]);
    }

    /** @param array{class: class-string, metadata: array<string, mixed>} $decorated */
    private function loadOne(array $decorated, Closure $next): void
    {
        $class = $decorated['class'];
        $base = Regex::replace('/^.*\\\/', '', $class);
        $metadata = $decorated['metadata'];
        $slug = $metadata['slug'];

        assert(is_a($class, Model::class, true));

        $resource = $class::query()->where('slug', $slug)->first();
        if ($resource && $this->option('new-only')) {
            return;
        }
        $resource?->delete();

        $this->info("LOAD: $slug [$base]");
        $resource = $class::fromSyncMetadata($metadata); // @phpstan-ignore-line
        $this->loaded++;
        $next($resource);
    }
}
