<?php

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Models\WpOrg\ClosedPlugin;
use App\Services\PluginServices;
use App\Values\WpOrg\PluginDTOs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginInformation_1_2_Controller extends Controller
{
    public function __construct(
        private readonly PluginServices\PluginInformationService $pluginInformationService,
        private readonly PluginServices\QueryPluginsService      $queryPluginsService,
        private readonly PluginServices\PluginHotTagsService     $hotTagsService,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $action = $request->query('action', '');

        $handlers = [
            'query_plugins' => fn() => $this->queryPlugins(PluginDTOs\QueryPluginsDTO::from($request)),
            'plugin_information' => fn() => $this->pluginInformation(PluginDTOs\PluginInformationDTO::from($request)),
            'hot_tags' => fn() => $this->hotTags($request),
            'popular_tags' => fn() => $this->hotTags($request),
        ];

        if (!isset($handlers[$action])) {
            return response()->json(['error' => 'Invalid action'], 400);
        }

        return $handlers[$action]();
    }

    /**
     * @param PluginDTOs\PluginInformationDTO $req
     * @return JsonResponse
     */
    private function pluginInformation(PluginDTOs\PluginInformationDTO $req): JsonResponse
    {
        $plugin = $this->pluginInformationService->findBySlug($req->slug);

        if (!$plugin) {
            return response()->json(['error' => 'Plugin not found'], 404);
        }

        $resource = PluginDTOs\PluginResponse::from($plugin);
        $status = 200;

        if ($plugin instanceof ClosedPlugin) {
            $resource = PluginDTOs\ClosedPluginResponse::from($plugin);
            $status = 404;
        }

        return response()->json($resource, $status);
    }

    /**
     * @param PluginDTOs\QueryPluginsDTO $request
     * @return JsonResponse
     */
    private function queryPlugins(PluginDTOs\QueryPluginsDTO $request): JsonResponse
    {
        $result = $this->queryPluginsService->queryPlugins($request);
        return response()->json($result);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    private function hotTags(Request $request): JsonResponse
    {
        $tags = $this->hotTagsService->getHotTags((int)$request->query('number', '-1'));
        return response()->json($tags);
    }
}
