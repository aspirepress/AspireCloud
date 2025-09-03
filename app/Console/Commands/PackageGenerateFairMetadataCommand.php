<?php

namespace App\Console\Commands;

use Exception;
use App\Models\Package;
use Illuminate\Console\Command;
use App\Values\Packages\FairMetadata;

use function Safe\json_encode;

class PackageGenerateFairMetadataCommand extends Command
{
    protected $signature = 'package:fair-metadata {did}';

    protected $description = 'Generate FAIR metadata for a package';

    public function handle(): void
    {
        $did = $this->argument('did');

        $package = Package::query()
            ->where('did', $did)
            ->first();
        if (!$package) {
            $this->error('Package not found');
            return;
        }

        $this->generateFairMetadata($package);
    }

    private function generateFairMetadata(Package $package): void
    {
        try {
            $metadata = FairMetadata::from($package);
            $fairData = $metadata->toArray();
            unset($fairData['raw_metadata']);

            // Here you would typically save the FAIR metadata to a file or database
            // For demonstration, we will just output it
            $this->info('Generated FAIR metadata:');
            $this->line(json_encode($fairData, JSON_PRETTY_PRINT));

        } catch (Exception $e) {
            $this->error("Error generating FAIR metadata: {$e->getMessage()}");
        }
    }
}
