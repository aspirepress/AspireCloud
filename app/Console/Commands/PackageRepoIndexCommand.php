<?php

namespace App\Console\Commands;

use Closure;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use App\Values\Packages\PackageData;
use App\Values\Packages\FairMetadata;

use function Safe\file_get_contents;
use function Safe\ini_set;
use function Safe\json_decode;

class PackageRepoIndexCommand extends Command
{
    protected $signature = 'package:repo-index {--stop-on-first-error}';

    protected $description = 'Import packages from FAIR repositories';

    private string $currentRepo = '';

    private int $errors = 0;

    private int $loaded = 0;

    public function handle(Pipeline $pipeline): void
    {
        ini_set('memory_limit', '-1');

        $repos = config('app.aspirecloud.fair.repos', '[]');
        if (empty($repos)) {
            $this->fail("No FAIR repositories configured. Update the FAIR_REPOS environment variable.");
        }

        $stages = [
            $this->readPackageMetadata(...),
            $this->createPackage(...),
        ];

        foreach ($repos as $repo) {
            $this->currentRepo = $repo;
            try {
                $packages = $this->getRepoPackages($repo);
                foreach ($packages as $did) {
                    try {
                        DB::transaction(fn() => $pipeline
                            ->send($did)
                            ->through($stages)
                            ->thenReturn());
                    } catch (Exception $e) {
                        $this->errors++;
                        $this->error("Package $did: {$e->getMessage()}");
                        $this->option('stop-on-first-error') and $this->fail("Errors encountered -- aborting.");
                    }
                }
            } catch (Exception $e) {
                $this->errors++;
                $this->error("Repo $this->currentRepo: {$e->getMessage()}");
                $this->option('stop-on-first-error') and $this->fail("Errors encountered -- aborting.");
            }
        }

        if ($this->errors > 0) {
            $this->fail("Indexed $this->loaded packages; $this->errors errors");
        }

        $this->info("Indexed $this->loaded packages.");
    }

    /** @return array<string, string> */
    private function getRepoPackages(string $repoUrl): array
    {
        $url = rtrim($repoUrl, '/') . '/wp-json/minifair/v1/packages';
        $this->info("Fetching packages from $url");

        $content = file_get_contents($url);
        if ($content == false) {
            throw new Exception("Failed to fetch $repoUrl");
        }
        $data = json_decode($content, true);
        if (!is_array($data)) {
            throw new Exception("Invalid JSON from $repoUrl");
        }
        return $data;
    }

    private function readPackageMetadata(string $did, Closure $next): void
    {
        $url = rtrim($this->currentRepo, '/') . '/wp-json/minifair/v1/packages/' . $did;
        $this->info("Fetching package metadata from $url");
        $content = file_get_contents($url);
        if ($content == false) {
            throw new Exception("Failed to fetch package metadata from $url");
        }
        $metadata = json_decode($content, true);
        if (!is_array($metadata)) {
            throw new Exception("Invalid JSON from $url");
        }
        $next($metadata);
    }

    /** @param array<string, mixed> $metadata */
    private function createPackage(array $metadata, Closure $next): void
    {
        try {
            $fairMetadata = FairMetadata::from($metadata);
            $package = PackageData::from($fairMetadata);
            $this->loaded++;
            $next($package);
        } catch (Exception $e) {
            throw new Exception("Failed to create package: {$e->getMessage()}");
        }
    }
}
