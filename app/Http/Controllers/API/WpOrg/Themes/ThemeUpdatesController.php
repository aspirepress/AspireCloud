<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Data\WpOrg\Themes\QueryThemesResponse;
use App\Data\WpOrg\Themes\ThemeInformationRequest;
use App\Data\WpOrg\Themes\ThemeUpdateCheckRequest;
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


            $themesToUpdate = Theme::query()
            ->when($updateRequest->themes, function ($query, $themes) {
                collect(array_keys($themes))->each(function ($slug) use ($query, $themes) {
                    $query->orWhere('slug', $slug)->where('version', '>', $themes[$slug]['Version']);
                });
            })->get();
            $themesNoUpdates = Theme::query()
            ->when($updateRequest->themes, function ($query, $themes) {
                collect(array_keys($themes))->each(function ($slug) use ($query, $themes) {
                    $query->orWhere('slug', $slug)->where('version', '<=', $themes[$slug]['Version']);
                });
            })->get();
            return $this->sendResponse([
                'themes' => new ThemeUpdateCollection(ThemeUpdateResource::collection($themesToUpdate)),
                'no_update' => new ThemeUpdateCollection(ThemeUpdateResource::collection($themesNoUpdates)),
                'translations' => TranslationResource::collection([]),

            ]);
        } catch (ValidationException $e) {

            // Handle validation errors and return a custom response
            $firstErrorMessage = collect($e->errors())->flatten()->first();
            return  $this->sendResponse(['error' => $firstErrorMessage], 400);
        }

    }

    /**
     * Send response based on API version.
     *
     * @param array<string,mixed>|AnonymousResourceCollection $response
     * @param int $statusCode
     * @return Response|JsonResponse
     */
    private function sendResponse(array|AnonymousResourceCollection $response, int $statusCode = 200): JsonResponse|Response
    {
        $version = request()->route('version');
        if ($version === '1.0') {
            return response(serialize((object) $response), $statusCode);
        }
        return response()->json($response, $statusCode);
    }
}
