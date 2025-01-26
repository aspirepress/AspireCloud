<?php

namespace App\Services\Downloads;

use App\Contracts\Downloads\Downloader;
use App\Enums\AssetType;
use App\Events\AssetCacheHit;
use App\Events\AssetCacheMissed;
use App\Models\WpOrg\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RedirectingDownloadService implements Downloader
{
    public const int TEMPORARY_URL_EXPIRE_MINS = 5;

    /**
     * Get the file download response. If the asset exists locally, return a redirect url to it.
     * Otherwise, redirect to WordPress.org and queue a job to download it for future requests.
     */
    public function download(AssetType $type, string $slug, string $file, ?string $revision = null): RedirectResponse
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
            return $this->response(
                Storage::temporaryUrl($asset->local_path, now()->addMinutes(self::TEMPORARY_URL_EXPIRE_MINS)),
            );
        }

        $upstreamUrl = $type->buildUpstreamUrl($slug, $file, $revision);

        event(
            new AssetCacheMissed(type: $type, slug: $slug, file: $file, upstreamUrl: $upstreamUrl, revision: $revision),
        );
        return $this->response($upstreamUrl);
    }

    private function response(string $url): RedirectResponse
    {
        return redirect()->away($url);
    }
}
