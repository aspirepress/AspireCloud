<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Exceptions\NotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Middleware\Hacks\InlineFairMetadata;
use App\Services\Themes\FeatureListService;
use App\Services\Themes\QueryThemesService;
use App\Services\Themes\ThemeHotTagsService;
use App\Services\Themes\ThemeInformationService;
use App\Utils\Regex;
use App\Values\WpOrg\Themes\QueryThemesRequest;
use App\Values\WpOrg\Themes\QueryThemesResponse;
use App\Values\WpOrg\Themes\ThemeInformationRequest;
use App\Values\WpOrg\Themes\ThemeResponse;
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
    )
    {
        // @mago-expect lint:middleware-in-routes
        config('feature.underscore_fair_hack') and $this->middleware(InlineFairMetadata::class);
    }

    public function info(Request $request): JsonResponse|Response
    {
        $action = (string)$request->query('action'); // @mago-expect analysis:array-to-string-conversion
        try {
            return match ($action) {
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
        $req = ThemeInformationRequest::fromRequest($request);
        $response = $this->themeInfo->info($req);
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
     * @param array<string,mixed>|QueryThemesResponse|ThemeResponse $response
     */
    private function sendResponse(
        array|QueryThemesResponse|ThemeResponse $response,
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
        $version = $request->route('version') ?? '1.2';
        if (version_compare($version, '1.2', '>=')) {
            return $request->query('wp_version');
        } elseif ($matches = Regex::match('|WordPress/([^;]+)|', $request->server('HTTP_USER_AGENT'))) {
            // Get version from user agent since it's not explicitly sent to feature_list requests in older API branches.
            return $matches[1];
        }
        return null;
    }
}
