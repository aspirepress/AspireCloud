<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadService;
use Safe\Exceptions\PcreException;
use Symfony\Component\HttpFoundation\Response;

class DownloadThemeController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    /**
     * @throws PcreException|PcreException
     */
    public function __invoke(string $file): Response
    {
        if (!\Safe\preg_match('/^([a-zA-Z0-9-]+)\.(.+)\.zip$/', $file, $matches)) {
            return response()->json(['error' => 'Invalid theme file format'], 400);
        }

        return $this->downloadService->download(
            AssetType::THEME_ZIP,
            $matches[1], // theme slug
            $file
        );
    }
}
