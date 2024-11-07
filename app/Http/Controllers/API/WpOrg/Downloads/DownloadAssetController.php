<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

class DownloadAssetController
{
    public function __invoke(string $slug, string $file): mixed
    {
        return 'DownloadAsset: ' . $slug . ' ' . $file;
    }
}
