<?php
declare(strict_types=1);

namespace App\Http\Middleware\Hacks;

use App\Services\Packages\PackageInformationService;
use App\Values\Packages\FairMetadata;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use function Safe\json_decode;

readonly class InlineFairMetadata
{

    public function __construct(
        private PackageInformationService $packageInfo,
    ) {}

    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        if (!$response instanceof Response) {
            return $response;
        }

        if (!($request->query('_fair') && $response->getStatusCode() === 200)) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content)) {
            return $response;
        }

        $body = json_decode($content, true);
        if (!is_array($body)) {
            return $response;
        }

        $slug = $body['slug'] ?? null;
        if (!$slug) {
            return $response;
        }

        $package = $this->packageInfo->findByDID("fake:$slug");
        $body['_fair'] = $package ? FairMetadata::from($package)->toArray() : null;

        $response->setContent(\Safe\json_encode($body));
        return $response;
    }
}
