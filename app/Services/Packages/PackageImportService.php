<?php

namespace App\Services\Packages;

use App\Models\Package;
use App\Values\Packages\PackageData;
use App\Values\Packages\FairMetadata;

class PackageImportService
{
    public function importPackage(string $json, bool $validateOnly = false, bool $newOnly = false): ?Package
    {
        $metadata = \Safe\json_decode($json, true);
        // Force validation.
        $fairMetadata = FairMetadata::from($metadata);

        if ($validateOnly) {
            return null;
        }

        $did = $metadata['id'];

        $package = Package::query()->where('did', $did)->first();
        if ($package && $newOnly) {
            return null;
        }
        $package?->delete();

        //$this->info("LOAD: $did");

        $package = Package::fromPackageData(PackageData::from($fairMetadata));
        return $package;
    }
}
