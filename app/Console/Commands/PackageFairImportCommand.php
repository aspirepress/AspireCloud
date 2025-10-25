<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Package;
use App\Utils\File;
use App\Values\Packages\FairMetadata;
use App\Values\Packages\PackageData;
use Closure;
use Exception;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use function Safe\ini_set;
use function Safe\json_decode;

class PackageFairImportCommand extends Command
{
    protected $signature = 'package:fair-import {file} {--validate-only} {--new-only} {--stop-on-first-error}';

    protected $description = 'Import packages from FAIR metadata stored in a ndjson file';

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
            $this->loadOne(...),
        ];

        foreach (File::lazyLines($filename) as $line) {
            $this->currentLine++;
            try {
                DB::transaction(fn() => $pipeline->send($line)->through($stages)->thenReturn());
            } catch (Exception $e) {
                $this->errors++;
                $this->error("Line $this->currentLine: {$e->getMessage()}");
                echo 'Partial line: ' . Str::substr($line, 0, 100) . "\n";
                $this->option('stop-on-first-error') and $this->fail('Errors encountered -- aborting.');
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
    private function loadOne(array $metadata, Closure $next): void
    {
        $did = $metadata['id'];

        $package = Package::query()->where('did', $did)->first();
        if ($package && $this->option('new-only')) {
            return;
        }
        $package?->delete();

        $this->info("LOAD: $did");

        $fairMetadata = FairMetadata::from($metadata);
        $package = Package::fromPackageData(PackageData::from($fairMetadata));
        $this->loaded++;
        $next($package);
    }
}
