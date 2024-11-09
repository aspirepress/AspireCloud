<?php

namespace App\Services\Downloads;

use App\Enums\AssetType;
use App\Jobs\DownloadAsset;
use App\Models\WpOrg\Asset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DownloadService
{
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
        // Check if we have it locally
        $asset = Asset::query()
            ->where('asset_type', $type->value)
            ->where('slug', $slug)
            ->where('local_path', 'LIKE', "%{$file}")
            ->when($revision, fn($q) => $q->where('revision', $revision))
            ->first();

        // If we have it, and it exists in storage, stream it
        if ($asset && Storage::exists($asset->local_path)) {
            return $this->downloadStoredFile($asset);
        }

        // If we don't have it, proxy from WordPress.org and queue download
        $upstreamUrl = $this->buildUpstreamUrl($type, $slug, $file, $revision);

        // Queue the download job and delay it by 10 seconds
        DownloadAsset::dispatch(
            $type,
            $slug,
            $file,
            $upstreamUrl,
            $revision
        )->delay(now()->addSeconds(10));

        return $this->downloadUpstreamFile($upstreamUrl);
    }

    /**
     * Download a file from our storage
     * Keep it simple and redirect using a temporary URL
     */
    private function downloadStoredFile(Asset $asset): RedirectResponse
    {
        $expirationInMinutes = 5;

        return redirect()->away(
            Storage::temporaryUrl(
                $asset->local_path,
                now()->addMinutes($expirationInMinutes)
            )
        );
    }

    /**
     * Proxy a file from WordPress.org
     * Keep it simple and redirect to the upstream URL
     */
    private function downloadUpstreamFile(string $upstreamUrl): Response
    {
        return redirect()->away($upstreamUrl);
    }

    /**
     * Build the WordPress.org upstream URL based on an asset type
     */
    private function buildUpstreamUrl(
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
