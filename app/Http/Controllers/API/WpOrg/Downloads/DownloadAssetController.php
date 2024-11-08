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

    public function __invoke(string $slug, string $file, ?string $rev = null): Response
    {
        $assetType = str_contains($file, 'screenshot-')
            ? AssetType::SCREENSHOT
            : AssetType::BANNER;

        return $this->downloadService->download(
            $assetType,
            $slug,
            $file,
            $rev
        );
    }
}
