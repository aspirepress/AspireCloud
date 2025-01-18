<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Response;

class DownloadPluginIconController
{
    public function __construct(private readonly DownloadService $downloadService) {}

    public function __invoke(string $slug, string $revision, string $file): Response
    {
        $type = AssetType::PLUGIN_GP_ICON;
        return $this->downloadService->download(type: $type, slug: $slug, file: $file, revision: $revision);
    }
}
