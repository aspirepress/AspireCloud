<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\FAIR\Packages;

use App\Values\DID\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Values\Packages\PackageInformationRequest;
use App\Services\Packages\PackageInformationService;

class PackageInformationController extends Controller
{
    public function __construct(
        private PackageInformationService $packageInfo,
    ) {}

    /**
     * @param Request $request
     * @return Response
     */
    public function didDocument(Request $request): Response
    {
        $type = $request->route('type');
        $slug = $request->route('slug');

        $package = $this->packageInfo->find($type, $slug);

        if (!$package) {
            abort(404, 'Package not found');
        }

        $didDocument = $this->generateDidDocument($package->did);

        return response(
            \Safe\json_encode($didDocument->toArray(), JSON_UNESCAPED_SLASHES) . "\n",
            200,
            [
                'Content-Type' => 'application/did+ld+json',
            ]
        );
    }

    /**
     * @param string $did
     * @return array<string, mixed>
     */
    public function fairMetadata(string $did): array
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

        return $package->_getRawMetadata(); // return raw data unmolested so signatures and extensions still work
        // return FairMetadata::from($package);
    }

    /**
     * @param string $did
     * @return Document
     */
    private function generateDidDocument(string $did): Document
    {
        return Document::from([
            'context' => 'https://w3id.org/did/v1',
            'alsoKnownAs' => [],
            'id' => $did,
            'service' => [
                [
                    'id'              => '#fairpm_repo',
                    'type'            => 'FairPackageManagementRepo',
                    'serviceEndpoint' => $this->packageInfo->getPackageMetadataUrl($did),
                ],
            ],
            'verificationMethod' => [],
        ]);
    }
}
