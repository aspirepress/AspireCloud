<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Http\Controllers\Controller;
use App\DTO\ApiResultsResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{
    /** @var array<string, string> $actions */
    private array $actions = [
        'query_themes' => 'doQueryThemes',
    ];

    /**
    * Handle API action and route the request to the appropriate method.
    *
    * @return JsonResponse|Response
    */
    public function info(Request $request): JsonResponse|Response
    {
        // version is passed as route parameter
        $action = $request->query('action');

        if (!array_key_exists($action, $this->actions)) {
            return $this->sendResponse(['error' => 'Action not implemented. <a href="https://codex.wordpress.org/WordPress.org_API">API Docs</a>";}'], 404);
        }
        $actionMethod = $this->actions[$action];
        $requestData = $request->query('request');

        $response = $this->$actionMethod($request, $requestData);

        return $this->sendResponse($response);
    }

    /**
     * Perform theme query based on the request data.
     *
     * @param array<string, string> $requestData
     * @return ApiResultsResponse
     */
    private function doQueryThemes(Request $request, array $requestData): ApiResultsResponse
    {
        $page = intval($request->input('page', 1));

        $perPage = intval($requestData['per_page']);
        $skip = ($page - 1) * $perPage;
        $themes = DB::table('themes')->skip($skip)->take($perPage)->get()->toArray();
        $total = DB::table('themes')->count();
        return new ApiResultsResponse('themes', $themes, $page, $perPage, $total);
    }

    /**
    * Send response based on API version.
    *
    * @param mixed $data
    * @param int $statusCode
    * @return Response|JsonResponse
    */
    private function sendResponse($data, $statusCode = 200): JsonResponse|Response
    {
        $version = request()->route('version');
        if ($version == '1.0') {
            if (is_object($data) && method_exists($data, 'toStdClass')) {
                $data = $data->toStdClass();
            }
            return response(serialize((object) $data), $statusCode);
        }
        return response()->json($data, $statusCode);
    }

}
