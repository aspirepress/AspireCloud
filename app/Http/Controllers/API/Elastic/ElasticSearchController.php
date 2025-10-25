<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\Elastic;

use App\Actions\Elastic\SearchPlugins;
use App\Http\Controllers\Controller;
use App\Values\WpOrg\Plugins\ElasticPluginsRequest;
use Illuminate\Http\JsonResponse;

class ElasticSearchController extends Controller
{
    public function searchPlugins(ElasticPluginsRequest $elasticPluginsRequest): JsonResponse
    {
        // TODO: refactor this weirdness
        return response()->json((new SearchPlugins($elasticPluginsRequest::fromRequest(request())))());
    }
}
