<?php

namespace App\Http\Controllers\API\FAIR\Packages;

use App\Http\Controllers\Controller;
use App\Services\Packages\PackageInformationService;
use App\Values\Packages\FairMetadata;
use App\Values\Packages\PackageInformationRequest;

class PackageInformationController extends Controller
{
    public function __construct(
        private PackageInformationService $packageInfo,
    ) {}

    /** @return array<string, mixed> */
    public function __invoke(string $did): array
    {
        return $this->packageInformation(new PackageInformationRequest($did));
    }

    /** @return array<string, mixed> */
    private function packageInformation(PackageInformationRequest $req): array
    {
        $package = $this->packageInfo->findByDID($req->did);

        if (!$package) {
            abort(404, 'Package not found');
        }

        return $package->raw_metadata;  // return raw data unmolested so signatures and extensions still work
        // return FairMetadata::from($package);
    }
}
