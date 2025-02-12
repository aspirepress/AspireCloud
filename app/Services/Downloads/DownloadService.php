<?php

namespace App\Services\Downloads;

use App\Contracts\Downloads\Downloader;
use App\Enums\AssetType;
use App\Events\AssetCacheHit;
use App\Events\AssetCacheMissed;
use App\Models\WpOrg\Asset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DownloadService implements Downloader
{
    public function download(AssetType $type, string $slug, string $file, ?string $revision = null): Response
    {
        Log::debug("DOWNLOAD", compact("type", "slug", "file", "revision"));

        if ($revision === 'head') {
            // head is there to have something in the url, but it behaves the same as not passing it
            $revision = null;
        }
        // Check if we have it locally
        $asset = Asset::query()
            ->where('asset_type', $type->value)
            ->where('slug', $slug)
            ->where('local_path', 'LIKE', "%{$file}")
            ->when($revision, fn($q) => $q->where('revision', $revision))
            ->orderBy('revision', 'desc')
            ->first();

        if ($asset && Storage::exists($asset->local_path)) {
            // TODO: handle case where asset exists but local path does not (DownloadAssetJob always creates a new Asset)
            event(new AssetCacheHit($asset));
            Log::debug("Serving existing asset", ["asset" => $asset]);
            $stream = Storage::disk('s3')->getDriver()->readStream($asset->local_path);
            return response()->stream(
                fn() => fpassthru($stream),
                headers: ['Content-Type' => $asset->getContentType()],
            );
        }

        $upstreamUrl = $type->buildUpstreamUrl($slug, $file, $revision);

        event(new AssetCacheMissed(type: $type, slug: $slug, file: $file, upstreamUrl: $upstreamUrl, revision: $revision));

        // TODO: use a real client.  Plugins are small enough we can get away with this for now.
        $response = Http::withHeaders(['User-Agent' => 'AspireCloud'])->get($upstreamUrl);
        return new Response($response->body(), $response->status(), $response->headers());
    }
}
