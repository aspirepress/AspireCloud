<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Http\Controllers\Controller;
use App\Models\ApiResultsResponse;

class ThemeController extends Controller
{
    private $actions = [
        'query_themes' => 'doQueryThemes',
    ];

    public function sendResponse($data, $statusCode = 200)
    {
        $version = request()->route('version');
        if ($version == '1.0') {
            if (is_object($data) && method_exists($data, 'toStdClass')) {
                $data = $data->toStdClass();
            }
            return response(serialize((object)$data), $statusCode);
        }
        return response()->json($data, $statusCode);
    }

    public function info()
    {
        // version is passed as route parameter
        $action = request()->query('action');

        if (!isset($this->actions[$action])) {
            return $this->sendResponse(['error' => 'Action not implemented. <a href="https://codex.wordpress.org/WordPress.org_API">API Docs</a>";}'], 404);
        }
        $actionMethod = $this->actions[$action];
        $requestData = request()->query('request');

        $response = $this->$actionMethod($requestData);

        return $this->sendResponse($response);
    }

    private function doQueryThemes($requestData)
    {
        $page = $requestData['page'];
        $perPage = $requestData['per_page'];
        $skip = ($page - 1) * $perPage;
        $themes = \DB::table('themes')->skip($skip)->take($perPage)->get()->toArray();
        $total = \DB::table('themes')->count();
        return new ApiResultsResponse('themes', $themes, $page, $perPage, $total);
    }
}
