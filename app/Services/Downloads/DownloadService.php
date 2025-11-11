<?php
declare(strict_types=1);

namespace App\Services\Downloads;

use App\Contracts\Downloads\Downloader;
use App\Enums\AssetType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadService implements Downloader
{
    public function download(
        Request $request,
        AssetType $type,
        string $slug,
        string $file,
        ?string $revision = null,
    ): Response
    {
        if ($revision === 'head') {
            // head is there to have something in the url, but it behaves the same as not passing it
            $revision = null;
        }

        try {
            $s3 = Storage::disk('s3');
        } catch (\Exception) {
            // HACK: S3 is not configured, so redirect everything back to .org after all
            // FIXME: use FILESYSTEM_DISK from config and deal with Laravel's godawful Storage facade
            $upstream_url = $type->buildUpstreamUrl($slug, $file, $revision);
            $context = [
                'type' => $type->value,
                'slug' => $slug,
                'file' => $file,
                'revision' => $revision,
                'upstream_url' => $upstream_url,
            ];
            Log::warning("Could not instantiate S3 storage -- redirecting to original URL", $context);
            return redirect()->to($upstream_url);
        }

        $context = ['type' => $type->value, 'slug' => $slug, 'file' => $file, 'revision' => $revision];
        Log::debug("DOWNLOAD", $context);

        $path = $type->buildLocalPath($slug, $file, $revision);

        $s3->exists($path) or $this->downloadAsset($type, $slug, $file, $revision);

        return $request->headers->has('cdn-loop') ? $this->passThroughToS3($path) : $this->redirectToS3($path);
    }

    public function downloadAsset(AssetType $type, string $slug, string $file, ?string $revision = null): void
    {
        $s3 = Storage::disk('s3');
        $path = $type->buildLocalPath($slug, $file, $revision);
        $upstream_url = $type->buildUpstreamUrl($slug, $file, $revision);
        $context = [
            'type' => $type->value,
            'slug' => $slug,
            'file' => $file,
            'revision' => $revision,
            'upstream_url' => $upstream_url,
        ];

        Log::debug("Downloading $file from $upstream_url", $context);

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
        ])->get($upstream_url);

        if (!$response->successful()) {
            $context['status'] = $response->getStatusCode();
            $context['message'] = $response->getReasonPhrase();
            Log::error("Failed to download asset for $slug:$revision", $context);
            abort($context['status'], $context['message']);
        }

        $s3->put($path, $response->resource());
    }

    private function redirectToS3(string $path): RedirectResponse
    {
        $s3 = Storage::disk('s3');
        return redirect($s3->temporaryUrl($path, now()->addSeconds(60)));
    }

    private function passThroughToS3(string $path): Response
    {
        $s3 = Storage::disk('s3');
        $url = $s3->temporaryUrl($path, now()->addSeconds(60));
        $headers = request()->headers->all();
        unset($headers['host']);
        $response = Http::withHeaders($headers)->get($url);
        $content = $response->body();
        return response($content, $response->status(), $response->headers());
    }
}
