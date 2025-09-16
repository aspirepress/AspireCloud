<?php

namespace App\Http\Controllers\API\FAIR\Packages;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Values\Packages\FairMetadata;
use App\Values\Packages\PackageInformationRequest;
use App\Services\Packages\PackageInformationService;

class PackageInformationController extends Controller
{
    public function __construct(
        private PackageInformationService $packageInfo,
    ) {}

    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function didDocument(Request $request): array
    {
        $type = $request->route('type');
        $slug = $request->route('slug');

        $package = $this->packageInfo->find($type, $slug);

        if (!$package) {
            abort(404, 'Package not found');
        }

        return $this->generateDidDocument($package->did);
    }

    public function fairMetadata(string $did): FairMetadata
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

    /**
     * @param string $did
     * @return array<string, mixed>
     */
    private function generateDidDocument(string $did): array
    {
        return [
            '@context' => 'https://w3id.org/did/v1',
            'alsoKnownAs' => [],
            'id' => $did,
            'service' => [
                [
                    'id'              => '#fairpm_repo',
                    'type'            => 'FairPackageManagementRepo',
                    'serviceEndpoint' => $this->packageInfo->getPackageMetadataUrl($did),
                ],
            ],
            'verificationMethod' => [
            ],
        ];
    }
}
