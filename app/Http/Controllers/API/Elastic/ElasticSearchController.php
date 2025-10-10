<?php

namespace App\Http\Controllers\API\Elastic;

use App\Actions\Elastic\SearchPlugins;
use App\Http\Controllers\Controller;
use App\Values\WpOrg\Plugins\ElasticPluginsRequest;
use Illuminate\Http\JsonResponse;

class ElasticSearchController extends Controller
{
    /**
     * Search plugins
     *
     * @param ElasticPluginsRequest $elasticPluginsRequest
     * @return JsonResponse
     */
    public function searchPlugins(ElasticPluginsRequest $elasticPluginsRequest): JsonResponse
    {
        return response()->json((new SearchPlugins($elasticPluginsRequest::fromRequest(request())))());
    }

    /**
     * Search themes
     * @return JsonResponse
     */
    public function searchThemes(): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }

    /**
     * Search packages
     * @return JsonResponse
     */
    public function searchPackages(): JsonResponse
    {
        return response()->json(['message' => 'Not implemented'], 501);
    }
}
