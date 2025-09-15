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

    public function __invoke(string $did): FairMetadata
    {
        return $this->packageInformation(new PackageInformationRequest($did));
    }

    private function packageInformation(PackageInformationRequest $req): FairMetadata
    {
        $package = $this->packageInfo->findByDID($req->did);

        if (!$package) {
            abort(404, 'Package not found');
        }

        return FairMetadata::from($package);
    }
}
