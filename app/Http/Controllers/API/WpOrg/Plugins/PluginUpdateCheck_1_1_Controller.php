<?php

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Http\Resources\Plugins\PluginUpdateCollection;
use App\Services\Plugins\PluginUpdateService;
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

        $response = [
            'plugins' => new PluginUpdateCollection($result['updates']),
            'translations' => [],
        ];

        // Only include no_update when 'all' parameter is true
        if ($req->all) {
            $response['no_update'] = new PluginUpdateCollection($result['no_updates']);
        }

        return response()->json($response);
    }
}
