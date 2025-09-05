<?php

namespace App\Http\Controllers\API\FAIR\Packages;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Values\Packages\PackageInformationRequest;
use App\Services\Packages\PackageInformationService;
use App\Values\Packages\FairMetadata;

class PackageInformationController extends Controller
{
    public function __construct(
        private PackageInformationService $packageInfo,
    ) {}

    public function __invoke(string $did): JsonResponse
    {
        return $this->packageInformation(new PackageInformationRequest($did));
    }

    private function packageInformation(PackageInformationRequest $req): JsonResponse
    {
        $package = $this->packageInfo->findByDID($req->did);

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        $resource = FairMetadata::from($package);

        return response()->json($resource, 200);
    }
}
