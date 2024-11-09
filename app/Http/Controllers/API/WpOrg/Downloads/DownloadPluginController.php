<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadService;
use Symfony\Component\HttpFoundation\Response;

class DownloadPluginController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    public function __invoke(string $file): Response
    {
        if (!\Safe\preg_match('/^([a-zA-Z0-9-]+)\.(.+)\.zip$/', $file, $matches)) {
            return response()->json(['error' => 'Invalid plugin file format'], 400);
        }

        $slug = $matches[1];

        return $this->downloadService->download(
            AssetType::PLUGIN,
            $slug,
            $file
        );
    }
}
