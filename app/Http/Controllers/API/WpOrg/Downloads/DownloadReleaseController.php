<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Response;

class DownloadReleaseController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    public function __invoke(string $version, string $extension): Response
    {
        if (!\Safe\preg_match('/^\d+\.\d+(?:\.\d+)?(?:-no-content|-new-bundled)?$/', $version)) {
            return response()->json(['error' => 'Invalid WordPress version format'], 400);
        }

        $file = "wordpress-{$version}.{$extension}";

        return $this->downloadService->download(type: AssetType::RELEASE, slug: 'wordpress', file: $file);
    }
}
