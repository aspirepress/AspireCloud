<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Models\WpOrg\ClosedPlugin;
use App\Services\PluginServices\PluginHotTagsService;
use App\Services\PluginServices\PluginInformationService;
use App\Services\PluginServices\QueryPluginsService;
use App\Values\WpOrg\Plugins\ClosedPluginResponse;
use App\Values\WpOrg\Plugins\PluginInformationRequest;
use App\Values\WpOrg\Plugins\PluginResponse;
use App\Values\WpOrg\Plugins\QueryPluginsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginInformation_1_0_Controller extends Controller
{
    public function __construct(private readonly PluginInformationService $pluginInfo) {}

    public function __invoke(string $slug): JsonResponse
    {
        return $this->pluginInformation(new PluginInformationRequest($slug));
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
}
