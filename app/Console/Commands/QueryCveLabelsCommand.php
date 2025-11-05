<?php
declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\PackageRelease;
use App\Models\Labels;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Query CVE Labeller API for Latest Package Releases
 *
 * This command checks the LATEST RELEASE of ALL packages for vulnerabilities.
 * The --severity flag determines which subset of packages to check in this run:
 *
 * --severity=high   : Checks packages where latest release currently has HIGH vulnerabilities
 * --severity=medium : Checks packages where latest release currently has MEDIUM vulnerabilities
 * --severity=low    : Checks packages where latest release currently has LOW vulnerabilities
 * --severity=none   : Checks packages where latest release has NO known vulnerabilities
 * (no flag)         : Checks ALL packages' latest releases
 *
 * The --package flag allows checking a single specific package:
 * --package=woocommerce     : Checks only the WooCommerce package by slug
 * --package=did:plc:xxx     : Checks only the package with the specified DID
 *
 * Scheduler runs different severity levels at different frequencies:
 * - High severity: Every 10 minutes (critical vulnerabilities need frequent monitoring)
 * - Medium severity: Every 30 minutes
 * - Low severity: Every 1 hour
 * - No vulnerabilities: Every 2 hours (to catch newly discovered issues)
 */
class QueryCveLabelsCommand extends Command
{
    protected $signature = 'cve:query {--severity= : Filter by severity: high, medium, low, or none} {--package= : Query a specific package by slug or DID}';

    protected $description = 'Query CVE Labeller API for latest package release vulnerability information';

    private function getApiUrl(): string
    {
        return config('cve.api_url', env('CVE_LABELLER_API_URL', 'http://api.cve-labeller.local/api/query'));
    }

    private function getTimeout(): int
    {
        return (int) config('cve.api_timeout', env('CVE_LABELLER_API_TIMEOUT', 30));
    }

    private function getBatchSize(): int
    {
        return (int) config('cve.batch_size', env('CVE_LABELLER_BATCH_SIZE', 50));
    }

    public function handle(): int
    {
        if (!$this->isEnabled()) {
            $this->info('CVE Labeller scanning is disabled');
            return self::SUCCESS;
        }

        $severity = $this->option('severity');
        $packageFilter = $this->option('package');

        // Build description of what we're querying
        $description = 'Querying CVE Labeller API for ';

        if ($packageFilter) {
            $description .= "package '{$packageFilter}'";
        } elseif ($severity) {
            $description .= "latest releases with {$severity} severity";
        } else {
            $description .= "ALL latest releases";
        }

        $this->info($description);

        // Get latest releases based on filters
        $latestReleases = $this->getLatestReleases($severity, $packageFilter);

        if ($latestReleases->isEmpty()) {
            $this->info('No package releases to query');
            return self::SUCCESS;
        }

        $this->info("Found {$latestReleases->count()} latest package release(s) to check");

        $batchSize = $this->getBatchSize();
        $chunks = $latestReleases->chunk($batchSize);
        $progressBar = $this->output->createProgressBar($chunks->count());
        $progressBar->start();

        foreach ($chunks as $chunk) {
            $this->processChunk($chunk);
            $progressBar->advance();

            // Add optional delay between batches to avoid overwhelming the API
            $delay = (int) config('cve.batch_delay', env('CVE_BATCH_DELAY', 0));
            if ($delay > 0) {
                usleep($delay * 1000); // Convert milliseconds to microseconds
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('CVE query completed successfully');

        return self::SUCCESS;
    }

    /**
     * Check if CVE scanning is enabled
     */
    public function isEnabled(): bool
    {
        return (bool) config('cve.enabled', env('CVE_LABELLER_ENABLED', true));
    }

    /**
     * Get latest release for each package, optionally filtered by current vulnerability severity and/or specific package
     *
     * This method:
     * 1. Finds the latest release for EACH package (based on created_at timestamp)
     * 2. Optionally filters to only packages where that latest release has a specific severity
     * 3. Optionally filters to a single specific package by slug or DID
     *
     * Examples:
     * - getLatestReleases(null, null): Returns latest release of ALL packages
     * - getLatestReleases('high', null): Returns latest releases that currently have HIGH severity vulnerabilities
     * - getLatestReleases('none', null): Returns latest releases that have NO known vulnerabilities
     * - getLatestReleases(null, 'woocommerce'): Returns latest release of WooCommerce package only
     * - getLatestReleases(null, 'did:plc:xxx'): Returns latest release of package with specific DID
     * - getLatestReleases('high', 'woocommerce'): Returns WooCommerce's latest release if it has HIGH severity
     */
    private function getLatestReleases(?string $severity, ?string $packageFilter): \Illuminate\Support\Collection
    {
        // Start with all packages
        $query = Package::query();

        // Filter by specific package if requested
        if ($packageFilter) {
            if (str_starts_with($packageFilter, 'did:')) {
                // Filter by DID
                $query->where('did', $packageFilter);
            } else {
                // Filter by slug
                $query->where('slug', $packageFilter);
            }
        }

        if ($severity) {
            $query->whereHas('releases', function ($q) use ($severity) {
                // Subquery to find the latest release ID
                $q->whereIn('id', function ($subquery) {
                    $subquery->select('id')
                             ->from('package_releases as pr')
                             ->whereColumn('pr.package_id', 'packages.id')
                             ->orderBy('pr.created_at', 'desc')
                             ->limit(1);
                })->whereHas('labels', function ($labelQ) use ($severity) {
                    $labelQ->where('value', $severity);
                });
            });
        }

        // Get packages with their latest release
        $packages = $query->with(['releases' => function ($releaseQuery) {
            $releaseQuery->orderBy('created_at', 'desc')->limit(1);
        }])->get();

        // Extract just the latest releases
        return $packages->map(function ($package) {
            return $package->releases->first(); // Latest release due to ordering
        })->filter(); // Remove any nulls
    }

    /**
     * Process a chunk of package releases
     */
    private function processChunk($chunk): void
    {
        try {
            $ids = $chunk->map(function (PackageRelease $release) {
                return $this->buildPackageIdentifier($release);
            })->filter()->implode(',');

            if ($ids === '') {
                return;
            }

            if (config('cve.log_api_requests', env('CVE_LOG_API_REQUESTS', false))) {
                Log::debug('CVE API Request', [
                    'ids'   => $ids,
                    'count' => $chunk->count(),
                ]);
            }

            /** @var HttpResponse $response */
            $response = $this->queryApiWithRetry($ids);

            if (! $response->successful()) {
                Log::error('CVE API request failed', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return;
            }

            $labels = $response->json();

            if (! is_array($labels)) {
                Log::error('CVE API returned invalid response', [
                    'response' => $response->body(),
                ]);
                return;
            }

            if (config('cve.log_api_requests', env('CVE_LOG_API_REQUESTS', false))) {
                Log::debug('CVE API Response', ['label_count' => count($labels)]);
            }

            $this->storeLabels($labels, $chunk);

        } catch (\Throwable $e) {
            Log::error('Error processing CVE labels chunk', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Query API with retry logic (always returns a Response)
     */
    private function queryApiWithRetry(string $ids): HttpResponse
    {
        $attempts   = (int) config('cve.retry_attempts', env('CVE_RETRY_ATTEMPTS', 3));
        $delaySec   = (int) config('cve.retry_delay', env('CVE_RETRY_DELAY', 5));
        $multiplier = (float) config('cve.retry_multiplier', env('CVE_RETRY_MULTIPLIER', 2));

        $delayFn = function (int $attempt, ?HttpResponse $response = null) use ($delaySec, $multiplier) {
            // $attempt starts at 1, so subtract 1 to start at base delay for the first retry
            return (int) ($delaySec * 1000 * pow($multiplier, $attempt - 1));
        };

        $result = Http::timeout($this->getTimeout())
                      ->retry($attempts, $delayFn, throw: false)
                      ->get($this->getApiUrl(), ['ids' => $ids]);

        // Ensure we always return a concrete Response
        if ($result instanceof PromiseInterface) {
            /** @var HttpResponse $resolved */
            $resolved = $result->wait();
            return $resolved;
        }

        /** @var HttpResponse $result */
        return $result;
    }

    /**
     * Build package identifier for API query
     *
     * Format: "fairpm:{did}/releases/{version}" or "fairpm:{slug}/releases/{version}"
     *
     * The 'did' and 'version' are columns in the database:
     * - did: The package's decentralized identifier (e.g., "did:plc:e3rm6t7cspgpzaf47kn3nnsl")
     * - version: The release version (e.g., "3.2.8.1")
     *
     * Examples:
     * - fairpm:did:plc:e3rm6t7cspgpzaf47kn3nnsl/releases/3.2.8.1
     * - fairpm:woocommerce/releases/9.3.3
     */
    private function buildPackageIdentifier(PackageRelease $release): ?string
    {
        $package = $release->package;

        if (!$package) {
            return null;
        }

        if ($package->did && str_starts_with($package->did, 'did:plc:')) {
            return "fairpm:{$package->did}/releases/{$release->version}";
        }

        return "fairpm:{$package->slug}/releases/{$release->version}";
    }

    /**
     * Store vulnerability labels in database
     */
    private function storeLabels(array $labels, $releases): void
    {
        // Create a map of package identifiers to releases for quick lookup
        $releaseMap = $releases->mapWithKeys(function (PackageRelease $release) {
            $identifier = $this->buildPackageIdentifier($release);
            return [$identifier => $release];
        });

        DB::transaction(function () use ($labels, $releaseMap) {
            foreach ($labels as $label) {
                if (!isset($label['subject']) || !isset($label['value'])) {
                    continue;
                }

                $subject = $label['subject'];
                $type = $label['type'];
                $class = $label['class'];
                $release = $releaseMap[$subject] ?? null;

                if (!$release) {
                    // Try to find release by parsing the subject
                    $release = $this->findReleaseFromSubject($subject);
                }

                if (!$release) {
                    Log::warning('Could not find release for subject', ['subject' => $subject]);
                    continue;
                }

                $value = Labels::parsevalue($label['value']);

                if (empty($value)) {
                    continue;
                }

                // Update or create the vulnerability label
                $vulnerabilityLabel = Labels::updateOrCreate(
                    [
                        'package_release_id' => $release->id,
                    ],
                    [
                        'type' => $type,
                        'class' => $class,
                        'value' => $value,
                        'source' => $label['source'] ?? 'unknown',
                        'data' => json_encode($label),
                        'updated_at' => now(),
                    ]
                );

                // Send alert if high severity and alerts are enabled
                if ($value === Labels::VALUE_HIGH && $this->shouldSendAlert($vulnerabilityLabel)) {
                    $this->sendAlert($vulnerabilityLabel);
                }
            }
        });
    }

    /**
     * Find package release from subject identifier
     *
     * Parses subjects like:
     * - "fairpm:did:plc:xxx/releases/1.0.0"
     * - "fairpm:woocommerce/releases/9.3.3"
     */
    private function findReleaseFromSubject(string $subject): ?PackageRelease
    {
        // Parse subject: "fairpm:{identifier}/releases/{version}"
        if (!preg_match('#^fairpm:([^/]+)/releases/(.+)$#', $subject, $matches)) {
            return null;
        }

        $packageIdentifier = $matches[1];
        $version = $matches[2];

        // Try to find by DID first (DIDs start with "did:")
        if (str_starts_with($packageIdentifier, 'did:')) {
            return PackageRelease::whereHas('package', function ($q) use ($packageIdentifier) {
                $q->where('did', $packageIdentifier);
            })->where('version', $version)->first();
        }

        // Try to find by slug
        return PackageRelease::whereHas('package', function ($q) use ($packageIdentifier) {
            $q->where('slug', $packageIdentifier);
        })->where('version', $version)->first();
    }

    /**
     * Check if alerts should be sent for this vulnerability
     */
    private function shouldSendAlert(Labels $label): bool
    {
        return (bool) config('cve.email_alerts_enabled', env('CVE_EMAIL_ALERTS_ENABLED', false));
    }

    /**
     * Send alert notification for high severity vulnerability
     */
    private function sendAlert(Labels $label): void
    {
        // Log the high severity finding
        Log::warning('High severity vulnerability detected', [
            'package_release_id' => $label->package_release_id,
            'value' => $label->value,
            'source' => $label->source,
        ]);
    }
}
