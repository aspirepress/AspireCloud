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
        $public = Storage::disk('public');
        $s3 = Storage::disk('s3');

        $context = ['type' => $type->value, 'slug' => $slug, 'file' => $file, 'revision' => $revision];
        Log::debug("DOWNLOAD", $context);

        if ($revision === 'head') {
            // head is there to have something in the url, but it behaves the same as not passing it
            $revision = null;
        }

        $asset = Asset::query()
            ->where('asset_type', $type->value)
            ->where('slug', $slug)
            ->where('local_path', 'LIKE', "%{$file}")
            ->when($revision, fn($q) => $q->where('revision', $revision))
            ->orderBy('revision', 'desc')
            ->first();

        $path = $asset?->local_path;
        $context['asset'] = $asset;

        if ($asset && !$public->exists($path) && $s3->exists($path)) {
            Log::debug("Copying $file from s3 to local filesystem", $context);
            $s3->copy($path, $public->path($path));
            // fall through to next case now that the file is on local
        }

        if ($asset && $public->exists($path)) {
            event(new AssetCacheHit($asset));
            Log::debug("Serving $file from local filesystem", $context);
            return redirect($public->url($path)); // must be 301 temp redirect, local files are not guaranteed.
        }

        if ($asset) {
            Log::info("Deleting stale asset for $file (neither local nor s3 paths exist)", $context);
            $asset->delete();
        }

        $upstreamUrl = $type->buildUpstreamUrl($slug, $file, $revision);
        $path = $type->buildLocalPath($slug, $file, $revision);
        $context['path'] = $path;

        Log::debug("Downloading $file from $upstreamUrl", $context);

        event(
            new AssetCacheMissed(type: $type, slug: $slug, file: $file, upstreamUrl: $upstreamUrl, revision: $revision),
        );

        $response = Http::withHeaders(['User-Agent' => 'AspireCloud'])->get($upstreamUrl);

        if (!$response->successful()) {
            $status = $response->status();
            $reason = $response->getReasonPhrase();
            Log::info("Asset download failed: $reason [url: $upstreamUrl]", [...$context, 'response' => $response]);
            abort($status, $reason);
        }

        Log::debug("Saving $file downloaded from $upstreamUrl", $context);

        $stream = $response->resource();
        $public->put($path, $stream);
        return redirect($public->url($path)); // must be 301 temp redirect, local files are not guaranteed.

        // return new Response($response->body(), $response->status(), $response->headers());
    }
}
