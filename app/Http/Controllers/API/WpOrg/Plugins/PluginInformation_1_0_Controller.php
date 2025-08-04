<?php

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Models\WpOrg\ClosedPlugin;
use App\Services\PluginServices\PluginHotTagsService;
use App\Services\PluginServices\PluginInformationService;
use App\Services\PluginServices\QueryPluginsService;
use App\Values\WpOrg\PluginDTOs\ClosedPluginResponse;
use App\Values\WpOrg\PluginDTOs\PluginInformationDTO;
use App\Values\WpOrg\PluginDTOs\PluginResponse;
use App\Values\WpOrg\PluginDTOs\QueryPluginsDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginInformation_1_0_Controller extends Controller
{
    public function __construct(private readonly PluginInformationService $pluginInfo) {}

    public function __invoke(string $slug): JsonResponse
    {
        return $this->pluginInformation(new PluginInformationDTO($slug));
    }

    private function pluginInformation(PluginInformationDTO $req): JsonResponse
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
}
