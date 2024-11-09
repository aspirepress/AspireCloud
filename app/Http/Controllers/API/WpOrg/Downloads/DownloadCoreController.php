<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Response;

class DownloadCoreController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    public function __invoke(string $version, string $extension): Response
    {
        if (!\Safe\preg_match('/^\d+\.\d+(?:\.\d+)?$/', $version)) {
            return response()->json(['error' => 'Invalid WordPress version format'], 400);
        }

        $file = "wordpress-{$version}.{$extension}";

        return $this->downloadService->download(
            AssetType::CORE,
            'wordpress',
            $file
        );
    }
}
