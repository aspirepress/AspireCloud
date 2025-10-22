<?php
declare(strict_types=1);

namespace App\Http\Middleware\Hacks;

use App\Models\Package;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use function Safe\json_decode;
use function Safe\json_encode;

readonly class InlineFairMetadata
{
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

        /** @var array<string,mixed> $body */
        $body = json_decode($content, true);
        if (!is_array($body)) {
            return $response;
        }

        $is_single = Arr::exists($body, 'slug');
        $is_plugins = Arr::exists($body, 'plugins');
        $is_themes = Arr::exists($body, 'themes');

        $slugs = match (true) {
            $is_single => [$body['slug']],
            $is_plugins => array_column($body['plugins'], 'slug'),
            $is_themes => array_column($body['themes'], 'slug'),
            default => [],
        };

        if (empty($slugs)) {
            return $response;
        }

        $fair_meta = Package::query()
            ->whereIn('slug', $slugs)
            ->pluck('raw_metadata', 'slug')
            ->toArray();

        if ($is_single) {
            $body = $this->insertPackage($body, $fair_meta);
        } elseif ($is_plugins) {
            $body['plugins'] = array_map(fn ($item) => $this->insertPackage($item, $fair_meta), $body['plugins']);
        } elseif ($is_themes) {
            $body['themes'] = array_map(fn ($item) => $this->insertPackage($item, $fair_meta), $body['themes']);
        } else {
            return $response;
        }

        $response->setContent(json_encode($body));
        return $response;
    }

    /**
     * @param array<string,mixed> $item
     * @param array<string,array<string,mixed>> $fair_meta
     * @return array<string,mixed>
     */
    private function insertPackage(array $item, array $fair_meta): array
    {
        $slug = $item['slug'] ?? null;
        if (!$slug) {
            return $item;
        }
        assert(is_string($slug));
        $fair = $fair_meta[$slug] ?? null;
        if (!$fair) {
            return $item;
        }

        return [...$item, '_fair' => $fair];
    }
}
