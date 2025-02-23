<?php

namespace App\Listeners;

use App\Events\AssetCacheMissed;
use App\Jobs\DownloadAssetJob;
use Illuminate\Support\Facades\Log;

class DownloadMissedAsset
{
    public function handle(AssetCacheMissed $event): void
    {
        Log::debug("Dispatching new DownloadAssetJob", ['event' => $event]);

        dispatch(
            new DownloadAssetJob(
                type: $event->type,
                slug: $event->slug,
                file: $event->file,
                upstreamUrl: $event->upstreamUrl,
                revision: $event->revision,
            ),
        );
    }
}
