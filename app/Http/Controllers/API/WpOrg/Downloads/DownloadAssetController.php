<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Response;

class DownloadAssetController
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    public function __invoke(string $slug, string $file): Response
    {
        $assetType = str_contains($file, 'screenshot-')
            ? AssetType::SCREENSHOT
            : AssetType::BANNER;

        // get the revision from the query string if it exists
        $rev = request()->query('rev') ?: null;

        return $this->downloadService->download(
            $assetType,
            $slug,
            $file,
            $rev
        );
    }
}
