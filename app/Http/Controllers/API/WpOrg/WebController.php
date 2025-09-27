<?php

namespace App\Http\Controllers\API\WpOrg;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\JsonResponse;

class WebController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return JsonResponse
     */
    public function showWebDid(string $slug): JsonResponse
    {
        // Validate the slug
        $packageBuilder = $this->validateSlug($slug);

        if (! $packageBuilder->exists()) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $package = $packageBuilder->first();

        $host = request()->getHost();

        $didPath = str_replace('/', ':', $slug);
        $did = 'did:web:' . $host . ':' . $didPath;

        $didDocument = [
            '@context' => [
                'https://www.w3.org/ns/did/v1',
                'https://w3id.org/security/multikey/v1',
                'https://w3id.org/security/suites/secp256k1-2019/v1',
            ],
            'alsoKnownAs' => [
                "https://wordpress.org/plugins/{$slug}",
                $package->origin ?? '',
            ],
            'id' => $did,
            'service' => [
                [
                    'id' => '#fairpm_repo',
                    'serviceEndpoint' => route('packages.show', $package->id),
                    'type' => 'FairPackageManagementRepo',
                ],
            ],
            'verificationMethod' => [
               //
            ],
        ];

        return response()->json($didDocument, 200, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Validate the slug
     *
     * @param  string  $slug
     * @return bool
     */
    private function validateSlug(string $slug): bool
    {
         return Package::query()->where('slug', $slug);
    }
}
