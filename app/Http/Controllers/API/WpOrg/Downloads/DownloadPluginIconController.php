<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadPluginIconController
{
    public function __construct(private readonly DownloadService $downloadService) {}

    public function __invoke(Request $request, string $slug, string $revision, string $file): Response
    {
        return $this->downloadService->download(
            request: $request,
            type: AssetType::PLUGIN_GP_ICON,
            slug: $slug,
            file: $file,
            revision: $revision,
        );
    }
}
