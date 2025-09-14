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

        // Let's hear it for N+1 selects!  Not going to optimize or cache these though, this is purely a dev experiment.

        if (isset($body['slug'])) {
            $body = $this->insertPackage($body);
        } elseif (is_array($body['plugins'] ?? null)) {
            $body['plugins'] = array_map($this->insertPackage(...), $body['plugins'] ?? []);
        } elseif (is_array($body['themes'] ?? null)) {
            $body['themes'] = array_map($this->insertPackage(...), $body['themes'] ?? []);
        } else {
            return $response;
        }

        $response->setContent(\Safe\json_encode($body));
        return $response;
    }

    /**
     * @param array<string,mixed> $item
     * @return array<string,mixed>
     */
    private function insertPackage(array $item): array
    {
        try {
            $slug = $item['slug'] ?? null;
            if ($slug === null) {
                return $item;
            }

            $package = $this->packageInfo->findByDID("fake:$slug");
            if (!$package) {
                return $item;
            }

            $metadata = FairMetadata::from($package)->toArray();
            return [...$item, '_fair' => $metadata];
        } catch (\Throwable $e) {
            report($e);
            return $item;
        }
    }
}
