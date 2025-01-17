<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Response;

class DownloadPluginAssetController
{
    public function __construct(private readonly DownloadService $downloadService) {}

    public function __invoke(string $slug, string $revision, string $file): Response
    {
        $type = str_contains($file, 'screenshot-') ? AssetType::PLUGIN_SCREENSHOT : AssetType::PLUGIN_BANNER;
        return $this->downloadService->download(type: $type, slug: $slug, file: $file, revision: $revision);
    }
}
