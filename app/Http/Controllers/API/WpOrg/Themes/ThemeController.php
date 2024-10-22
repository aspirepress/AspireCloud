<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Data\WpOrg\Themes\HotTagsRequest;
use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Http\Controllers\Controller;
use App\DTO\ApiResultsResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ThemeController extends Controller
{

    public function info(Request $request): JsonResponse|Response
    {
        $action = $request->query('action');
        $response = match ($action) {
            'query_themes' => $this->doQueryThemes(QueryThemesRequest::fromRequest($request)),
            'theme_information' => $this->doThemeInformation(ThemeInformationRequest::fromRequest($request)),
            'hot_tags' => $this->doHotTags($request),
            'feature_list' => $this->doFeatureList($request),
            default => $this->unknownAction()
        };
        return $this->sendResponse($response);
    }

    private function doQueryThemes(QueryThemesRequest $req): ApiResultsResponse
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $skip = ($page - 1) * $perPage;

        $themes = DB::table('themes')->skip($skip)->take($perPage)->get()->toArray();
        $total = DB::table('themes')->count();

        return new ApiResultsResponse('themes', $themes, $page, $perPage, $total);
    }

    private function doThemeInformation(ThemeInformationRequest $req): array
    {
        return ['req' => $req];
    }

    private function doHotTags(Request $request): array
    {
        return ['error' => 'not implemented'];
    }

    private function doFeatureList(Request $request): array
    {
        return ['error' => 'not implemented'];
    }


    private function unknownAction(): Response
    {
        return $this->sendResponse(['error' => 'Action not implemented. <a href="https://codex.wordpress.org/WordPress.org_API">API Docs</a>";}'],
            404);
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
        if ($version === '1.0') {
            if (is_object($data) && method_exists($data, 'toStdClass')) {
                $data = $data->toStdClass();
            }
            return response(serialize((object)$data), $statusCode);
        }
        return response()->json($data, $statusCode);
    }
}
