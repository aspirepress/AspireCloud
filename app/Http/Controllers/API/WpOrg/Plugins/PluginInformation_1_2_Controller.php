<?php

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Models\WpOrg\ClosedPlugin;
use App\Services\Plugins\PluginHotTagsService;
use App\Services\Plugins\PluginInformationService;
use App\Services\Plugins\QueryPluginsService;
use App\Values\WpOrg\Plugins\ClosedPluginResponse;
use App\Values\WpOrg\Plugins\PluginInformationRequest;
use App\Values\WpOrg\Plugins\PluginResponse;
use App\Values\WpOrg\Plugins\QueryPluginsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginInformation_1_2_Controller extends Controller
{
    public function __construct(
        private readonly PluginInformationService $pluginInfo,
        private readonly QueryPluginsService $queryPlugins,
        private readonly PluginHotTagsService $hotTags,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        return match ($request->query('action')) {
            'query_plugins' => $this->queryPlugins(QueryPluginsRequest::from($request)),
            'plugin_information' => $this->pluginInformation(PluginInformationRequest::from($request)),
            'hot_tags', 'popular_tags' => $this->hotTags($request),
            default => response()->json(['error' => 'Invalid action'], 400),
        };
    }

    private function pluginInformation(PluginInformationRequest $req): JsonResponse
    {
        $plugin = $this->pluginInfo->findBySlug($req->slug);

        if (!$plugin) {
            return response()->json(['error' => 'Plugin not found'], 404);
        }

        if ($plugin instanceof ClosedPlugin) {
            $resource = ClosedPluginResponse::from($plugin);
            $status = 404;
        } else {
            $resource = PluginResponse::from($plugin);
            $status = 200;
        }
        return response()->json($resource, $status);
    }

    private function queryPlugins(QueryPluginsRequest $request): JsonResponse
    {
        $result = $this->queryPlugins->queryPlugins($request);
        return response()->json($result);
    }

    private function hotTags(Request $request): JsonResponse
    {
        $tags = $this->hotTags->getHotTags((int) $request->query('number', '-1'));
        return response()->json($tags);
    }
}
