<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\QueryThemesResponse;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use function Safe\json_decode;

class ThemeController extends Controller
{
    public function info(Request $request): JsonResponse|Response
    {
        $action = $request->query('action');
        $response = match ($action) {
            'query_themes' => $this->doQueryThemes(QueryThemesRequest::fromRequest($request)),
            'theme_information' => $this->doThemeInformation(ThemeInformationRequest::fromRequest($request)),
            'hot_tags' => $this->doHotTags(),
            'feature_list' => $this->doFeatureList(),
            default => $this->unknownAction()
        };
        return $this->sendResponse($response);
    }

    private function doQueryThemes(QueryThemesRequest $req): QueryThemesResponse
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $skip = ($page - 1) * $perPage;

        // TODO: process search and other filters
        $themes = DB::table('themes')
            ->skip($skip)
            ->take($perPage)
            ->get()
            ->map(fn($theme) => json_decode($theme->metadata))
            ->toArray();

        $total = DB::table('themes')->count();

        $pageInfo = ['page' => $page, 'pages' => (int) ceil($total / $perPage), 'results' => $total];
        return QueryThemesResponse::from(['pageInfo' => $pageInfo, 'themes' => $themes]);
    }

    /** @return array<string, mixed> */
    private function doThemeInformation(ThemeInformationRequest $req): array
    {
        return ['req' => $req];
    }

    /** @return array<string, mixed> */
    private function doHotTags(): array
    {
        return ['error' => 'not implemented'];
    }

    /** @return array<string, mixed> */
    private function doFeatureList(): array
    {
        return ['error' => 'not implemented'];
    }


    private function unknownAction(): Response
    {
        return $this->sendResponse(
            ['error' => 'Action not implemented. <a href="https://codex.wordpress.org/WordPress.org_API">API Docs</a>";}'],
            404
        );
    }

    /**
     * Send response based on API version.
     *
     * @param array<string,mixed>|QueryThemesResponse $response
     * @param int $statusCode
     * @return Response|JsonResponse
     */
    private function sendResponse(array|QueryThemesResponse $response, int $statusCode = 200): JsonResponse|Response
    {
        $version = request()->route('version');
        if ($version === '1.0') {
            if (is_object($response) && method_exists($response, 'toStdClass')) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $response = $response->toStdClass();
            }
            return response(serialize((object) $response), $statusCode);
        }
        return response()->json($response, $statusCode);
    }
}
