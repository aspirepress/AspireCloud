<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Services\PluginServices\PluginUpdateService;
use App\Values\WpOrg\Plugins\PluginUpdateCheckRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginUpdateCheck_1_1_Controller extends Controller
{
    public function __construct(
        private readonly PluginUpdateService $updateService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // Bag's Laravel autoconversion doesn't work right, so do it by hand.
        $req = PluginUpdateCheckRequest::from($request);

        $result = $this->updateService->checkForUpdates($req);
        $req->all or $result = $result->withoutNoUpdate();  // we already generated the list, so just drop it

        return response()->json($result);
    }
}
