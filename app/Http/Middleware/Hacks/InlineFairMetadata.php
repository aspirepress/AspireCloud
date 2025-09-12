<?php
declare(strict_types=1);

namespace App\Http\Middleware\Hacks;

use App\Services\Packages\PackageInformationService;
use App\Values\Packages\FairMetadata;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function Safe\json_decode;

class InlineFairMetadata
{

    public function __construct(
        private PackageInformationService $packageInfo,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        if (!($request->query('_fair') && $response->getStatusCode() === 200)) {
            return $response;
        }

        $content = $response->getContent();
        $body = json_decode($content, true);
        $slug = $body['slug'] ?? null;
        if (!$slug) {
            return $response;
        }

        $package = $this->packageInfo->findByDID("fake:$slug");
        $body['_fair'] = $package ? FairMetadata::from($package)->toArray() : null;

        $response->setContent(json_encode($body));
        return $response;
    }
}
