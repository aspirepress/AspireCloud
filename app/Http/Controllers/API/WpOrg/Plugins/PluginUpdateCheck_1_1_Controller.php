<?php

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Http\Requests\Plugins\PluginUpdateRequest;
use App\Http\Resources\Plugins\PluginUpdateCollection;
use App\Services\PluginUpdateService;
use Illuminate\Http\JsonResponse;

use function Safe\json_decode;

class PluginUpdateCheck_1_1_Controller extends Controller
{
    public function __construct(
        private readonly PluginUpdateService $pluginService
    ) {}

    public function __invoke(PluginUpdateRequest $request): JsonResponse
    {
        $pluginsData = json_decode($request->plugins, true);

        $result = $this->pluginService->processPlugins(
            plugins: $pluginsData['plugins'],
            includeAll: $request->boolean('all')
        );

        $response = [
            'plugins' => new PluginUpdateCollection($result['updates']),
            'translations' => [],
        ];

        // Only include no_update when 'all' parameter is true
        if ($request->boolean('all')) {
            $response['no_update'] = new PluginUpdateCollection($result['no_updates']);
        }

        return response()->json($response);
    }
}
