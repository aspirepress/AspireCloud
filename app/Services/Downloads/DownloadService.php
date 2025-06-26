<?php

namespace App\Services\Downloads;

use App\Contracts\Downloads\Downloader;
use App\Enums\AssetType;
use App\Jobs\DownloadAssetJob;
use App\Models\WpOrg\Asset;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DownloadService implements Downloader
{

    public function download(AssetType $type, string $slug, string $file, ?string $revision = null): Response
    {
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

        if ($asset && $s3->exists($path)) {
            $url = $s3->temporaryUrl($path, now()->addMinutes(60));
            Log::debug("Serving $file from s3", [...$context, 'temp_url' => $url]);;
            return redirect($url);
        }

        if ($asset) {
            Log::info("Deleting stale asset for $file (missing on s3)", $context);
            $asset->delete();
        }

        $upstreamUrl = $type->buildUpstreamUrl($slug, $file, $revision);
        $path = $type->buildLocalPath($slug, $file, $revision);

        dispatch_sync(
            new DownloadAssetJob(
                type: $type,
                slug: $slug,
                file: $file,
                upstreamUrl: $upstreamUrl,
                revision: $revision,
            ),
        );

        return redirect($s3->temporaryUrl($path, now()->addMinutes(60)));
    }
}
