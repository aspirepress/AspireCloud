<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\FAIR\Packages;

use App\Models\Labels;
use App\Models\Package;
use App\Values\DID\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
     *
     * @return array|JsonResponse|mixed[]
     */
    public function fairMetadata(string $did)
    {
        return $this->packageInformation(new PackageInformationRequest($did));
    }

    /**
     * Get FAIR metadata for a package including CVE/vulnerability information
     *
     * @param PackageInformationRequest $req
     *
     * @return JsonResponse
     */
    private function packageInformation(PackageInformationRequest $req): JsonResponse
    {
        $package = $this->packageInfo->findByDID($req->did);

        if (!$package) {
            abort(404, 'Package not found');
        }

        $metadata = $package->_getRawMetadata(); // return raw data unmolested so signatures and extensions still work

        $metadata['security'] = $this->getSecurityInformation($package);

        return response()->json($metadata);
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

    /**
     * Get security/vulnerability information for a package
     *
     * Returns vulnerability data for all releases, with special emphasis on the latest release
     *
     * @param Package $package
     * @return array
     */
    private function getSecurityInformation(Package $package): array
    {
        // Get latest release
        $latestRelease = $package->releases()
                                 ->orderBy('created_at', 'desc')
                                 ->first();

        $security = [
            'latest_release' => null,
            'all_releases' => [],
            'summary' => [
                'has_vulnerabilities' => false,
                'highest_severity' => '',
                'total_vulnerable_releases' => 0,
                'last_scanned_at' => null,
            ],
        ];

        if (!$latestRelease) {
            return $security;
        }

        // Get vulnerability information for latest release
        $latestVulnerability = $latestRelease->labels()->first();

        $security['latest_release'] = [
            'version' => $latestRelease->version,
            'vulnerability' => $latestVulnerability ? [
                'status' => $latestVulnerability->value,
                'type' => $latestVulnerability->type,
                'class' => $latestVulnerability->class,
                'source' => $latestVulnerability->source,
                'scanned_at' => $latestVulnerability->updated_at->toIso8601String(),
                'details' => json_decode($latestVulnerability->data, true),
            ] : null,
        ];

        // Get vulnerability information for ALL releases
        $allReleases = $package->releases()
                               ->with('labels')
                               ->where('type', 'security')
                               ->orderBy('created_at', 'desc')
                               ->get();

        $vulnerableCount = 0;
        $highestSeverity = '';
        $lastScanned = null;

        foreach ($allReleases as $release) {
            $label = $release->labels()->first();

            $releaseVuln = [
                'version' => $release->version,
                'vulnerability' => null,
            ];

            if ($label) {
                $releaseVuln['vulnerability'] = [
                    'status' => $label->value,
                    'type' => $label->type,
                    'class' => $label->class,
                    'source' => $label->source,
                    'scanned_at' => $label->updated_at->toIso8601String(),
                ];

                // Update summary stats
                if ($label->value !== '') {
                    $vulnerableCount++;
                    $highestSeverity = $this->getHighestSeverity($highestSeverity, $label->value);
                }

                // Track most recent scan
                if (!$lastScanned || $label->updated_at > $lastScanned) {
                    $lastScanned = $label->updated_at;
                }
            }

            $security['all_releases'][] = $releaseVuln;
        }

        // Update summary
        $security['summary'] = [
            'has_vulnerabilities' => $vulnerableCount > 0,
            'highest_severity' => $highestSeverity,
            'total_vulnerable_releases' => $vulnerableCount,
            'last_scanned_at' => $lastScanned?->toIso8601String(),
        ];

        return $security;
    }

    /**
     * Determine the highest severity between two values
     *
     * @param string $current
     * @param string $new
     * @return string
     */
    private function getHighestSeverity(string $current, string $new): string
    {
        $severityOrder = [
            Labels::VALUE_LOW => 1,
            Labels::VALUE_MEDIUM => 2,
            Labels::VALUE_HIGH => 3,
        ];

        $currentLevel = $severityOrder[$current] ?? '';
        $newLevel = $severityOrder[$new] ?? '';

        return $newLevel > $currentLevel ? $new : $current;
    }
}
