<?php

namespace App\Services\Downloads;

use App\Enums\AssetType;
use App\Models\WpOrg\Asset;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            return $this->streamStoredFile($asset);
        }

        // If we don't have it, proxy from WordPress.org and queue download
        $upstreamUrl = $this->buildUpstreamUrl($type, $slug, $file, $revision);

        // TODO: Queue download job

        return $this->proxyUpstreamFile($upstreamUrl, $file);
    }

    /**
     * Stream a file from our storage
     */
    private function streamStoredFile(Asset $asset): StreamedResponse
    {
        $filename = basename($asset->local_path);
        $mimeType = $this->getMimeType($filename);

        return Storage::download(
            $asset->local_path,
            $filename,
            ['Content-Type' => $mimeType]
        );
    }

    /**
     * Proxy a file from WordPress.org
     */
    private function proxyUpstreamFile(string $upstreamUrl, string $filename): Response
    {
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        ])->get($upstreamUrl);

        if (!$response->successful()) {
            throw new NotFoundHttpException("File not found at upstream source");
        }

        return response($response->body(), 200, [
            'Content-Type'        => $this->getMimeType($filename),
            'Content-Disposition' => 'attachment; filename="' . basename($filename) . '"',
            'Content-Length'      => $response->header('Content-Length'),
        ]);
    }

    /**
     * Build the WordPress.org upstream URL based on asset type
     */
    private function buildUpstreamUrl(
        AssetType $type,
        string $slug,
        string $file,
        ?string $revision,
    ): string {
        $baseUrl = match ($type) {
            AssetType::CORE_ZIP => 'https://wordpress.org/',
            AssetType::PLUGIN_ZIP => 'https://downloads.wordpress.org/plugin/',
            AssetType::THEME_ZIP => 'https://downloads.wordpress.org/theme/',
            AssetType::SCREENSHOT,
            AssetType::BANNER => sprintf('https://ps.w.org/%s/assets/', $slug),
        };

        $url = $baseUrl . $file;

        if ($revision && $type->isAsset()) {
            $url .= "?rev={$revision}";
        }

        return $url;
    }

    /**
     * Get the MIME type for a file
     */
    private function getMimeType(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'zip' => 'application/zip',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }
}
