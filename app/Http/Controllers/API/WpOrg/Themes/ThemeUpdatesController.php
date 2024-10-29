<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\QueryThemesResponse;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Data\WpOrg\Themes\ThemeUpdateCheckRequest;
use App\Data\WpOrg\Themes\ThemeUpdateCheckResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeCollection;
use App\Http\Resources\ThemeUpdateCollection;
use App\Http\Resources\ThemeUpdateResource;
use App\Http\Resources\TranslationResource;
use App\Models\WpOrg\Theme;
use App\Models\WpOrg\SyncTheme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use function Safe\json_decode;
use function Safe\preg_match;

class ThemeUpdatesController extends Controller
{
    /**
    * @param Request $request
    *
    * @return JsonResponse
    */
    public function __invoke(Request $request): JsonResponse|Response
    {

        try {
            $updateRequest = ThemeUpdateCheckRequest::fromRequest($request);


            $themes = Theme::query()
            ->whereIn('slug', array_keys($updateRequest->themes))
            ->get()
            ->partition(function ($theme) use ($updateRequest) {
                return version_compare($theme->version, $updateRequest->themes[$theme->slug]['Version'], '>');
            });
            return $this->sendResponse(ThemeUpdateCheckResponse::fromData($themes[0], $themes[1]));
        } catch (ValidationException $e) {

            // Handle validation errors and return a custom response
            $firstErrorMessage = collect($e->errors())->flatten()->first();
            return  $this->sendResponse(['error' => $firstErrorMessage], 400);
        }

    }

    /**
     * Send response based on API version.
     *
     * @param array<string,mixed>|ThemeUpdateCheckResponse $response
     * @param int $statusCode
     * @return Response|JsonResponse
     */
    private function sendResponse(array|ThemeUpdateCheckResponse $response, int $statusCode = 200): JsonResponse|Response
    {
        $version = request()->route('version');
        if ($version === '1.0') {
            return response(serialize((object) $response), $statusCode);
        }
        return response()->json($response, $statusCode);
    }
}
