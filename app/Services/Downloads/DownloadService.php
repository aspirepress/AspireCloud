<?php

namespace App\Services\Downloads;

use App\Enums\AssetType;
use App\Events\AssetCacheHit;
use App\Events\AssetCacheMissed;
use App\Models\WpOrg\Asset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DownloadService
{
    public const int TEMPORARY_URL_EXPIRE_MINS = 5;

    /**
     * Get the file download response. If the asset exists locally,
     * streams it from storage. Otherwise, proxies the WordPress.org file
     * and queues a job to download it for future requests.
     */
    public function download(
        AssetType $type,
        string $slug,
        string $file,
        ?string $revision = null,
    ): Response {
        Log::debug("DOWNLOAD", compact("type", "slug", "file", "revision"));
        // Check if we have it locally
        $asset = Asset::query()
            ->where('asset_type', $type->value)
            ->where('slug', $slug)
            ->where('local_path', 'LIKE', "%{$file}")
            ->when($revision, fn($q) => $q->where('revision', $revision))
            ->first();

        // If we have it, and it exists in storage, stream it
        if ($asset && Storage::exists($asset->local_path)) {
            // TODO: handle case where asset exists but local path does not (DownloadAssetJob always creates a new Asset)
            event(new AssetCacheHit($asset));
            Log::debug("Serving existing asset", ["asset" => $asset]);
            return redirect()->away(
                Storage::temporaryUrl($asset->local_path, now()->addMinutes(self::TEMPORARY_URL_EXPIRE_MINS)),
            );
        }

        // If we don't have it, proxy from WordPress.org and queue download
        $upstreamUrl = self::buildUpstreamUrl($type, $slug, $file, $revision);

        event(new AssetCacheMissed(type: $type, slug: $slug, file: $file, upstreamUrl: $upstreamUrl, revision: $revision));
        return redirect()->away($upstreamUrl);
    }

    /** Build the WordPress.org upstream URL based on asset type and revision */
    private static function buildUpstreamUrl(
        AssetType $type,
        string $slug,
        string $file,
        ?string $revision,
    ): string {
        $baseUrl = match ($type) {
            AssetType::CORE => 'https://wordpress.org/',
            AssetType::PLUGIN => 'https://downloads.wordpress.org/plugin/',
            AssetType::THEME => 'https://downloads.wordpress.org/theme/',
            AssetType::SCREENSHOT,
            AssetType::BANNER => sprintf('https://ps.w.org/%s/assets/', $slug),
        };

        $url = $baseUrl . $file;

        if ($revision && $type->isAsset()) {
            $url .= "?rev={$revision}";
        }

        return $url;
    }
}
