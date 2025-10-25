<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Downloads;

use App\Enums\AssetType;
use App\Http\Controllers\Controller;
use App\Services\Downloads\DownloadService;
use App\Utils\Regex;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DownloadThemeController extends Controller
{
    public function __construct(
        private readonly DownloadService $downloadService,
    ) {}

    public function __invoke(Request $request, string $file): Response
    {
        $matches = Regex::match('/^([a-zA-Z0-9-]+)\.(.+)\.zip$/', $file);
        if (!$matches) {
            return response()->json(['error' => "Invalid filename", 'filename' => $file], 400);
        }

        return $this->downloadService->download($request, type: AssetType::THEME, slug: $matches[1], file: $file);
    }
}
