<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeCollection;
use App\Http\Resources\ThemeResource;
use App\Services\Themes\FeatureListService;
use App\Services\Themes\QueryThemesService;
use App\Services\Themes\ThemeHotTagsService;
use App\Services\Themes\ThemeInformationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

use function Safe\preg_match;

class ThemeController extends Controller
{
    public function __construct(
        private readonly QueryThemesService $queryThemes,
        private readonly ThemeInformationService $themeInfo,
        private readonly ThemeHotTagsService $hotTags,
        private readonly FeatureListService $featureList,
    ) {}

    public function info(Request $request): JsonResponse|Response
    {
        try {
            return match ($request->query('action')) {
                'query_themes' => $this->doQueryThemes($request),
                'theme_information' => $this->doThemeInformation($request),
                'hot_tags' => $this->doHotTags($request),
                'feature_list' => $this->doFeatureList($request),
                default => $this->unknownAction(),
            };
        } catch (ValidationException $e) {
            // Handle validation errors and return a custom response
            $firstErrorMessage = collect($e->errors())->flatten()->first();
            return $this->sendResponse(['error' => $firstErrorMessage], 400);
        } catch (NotFoundException $e) {
            return $this->sendResponse(['error' => $e->getMessage()], 404);
        }
    }

    private function doQueryThemes(Request $request): JsonResponse|Response
    {
        $req = QueryThemesRequest::from($request);
        $themes = $this->queryThemes->queryThemes($req);
        return $this->sendResponse($themes);
    }

    private function doThemeInformation(Request $request): JsonResponse|Response
    {
        // NOTE: upstream requires slug query parameter to be request[slug], just slug is not recognized
        $response = $this->themeInfo->info(ThemeInformationRequest::from($request));
        return $this->sendResponse($response);
    }

    private function doHotTags(Request $request): JsonResponse|Response
    {
        $tags = $this->hotTags->getHotTags((int) $request->query('number', '-1'));
        return $this->sendResponse($tags);
    }

    private function doFeatureList(Request $request): JsonResponse|Response
    {
        $wpVersion = $this->getWpVersion($request);
        $tags = $this->featureList->getFeatureList($wpVersion);
        return $this->sendResponse($tags);
    }

    private function unknownAction(): JsonResponse
    {
        return $this->sendResponse(
            ['error' => 'Action not implemented. <a href="https://codex.wordpress.org/WordPress.org_API">API Docs</a>";}'],
            404,
        );
    }

    /**
     * Send response based on API version.
     *
     * @param array<string,mixed>|ThemeCollection $response
     */
    private function sendResponse(
        array|ThemeCollection|ThemeResource $response,
        int $statusCode = 200,
    ): JsonResponse|Response {
        $version = request()->route('version');
        if ($version === '1.0') {
            return response(serialize((object) $response), $statusCode);
        }
        return response()->json($response, $statusCode);
    }

    private function getWpVersion(Request $request): ?string
    {
        $version = $request->route('version');
        if (version_compare($version, '1.2', '>=')) {
            return $request->query('wp_version');
        } elseif (preg_match('|WordPress/([^;]+)|', $request->server('HTTP_USER_AGENT'), $matches)) {
            // Get version from user agent since it's not explicitly sent to feature_list requests in older API branches.
            return $matches[1];
        }
        return null;
    }
}
