<?php

namespace App\Http\Controllers\API\WpOrg\Themes;

use App\Http\Controllers\Controller;
use App\Models\WpOrg\Theme;
use App\Values\WpOrg\Themes\ThemeUpdateCheckRequest;
use App\Values\WpOrg\Themes\ThemeUpdateCheckResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class ThemeUpdatesController extends Controller
{
    /**
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
            return $this->sendResponse(['error' => $firstErrorMessage], 400);
        }
    }

    /**
     * Send response based on API version.
     *
     * @param array<string,mixed>|ThemeUpdateCheckResponse $response
     */
    private function sendResponse(array|ThemeUpdateCheckResponse $response, int $statusCode = 200): JsonResponse|Response
    {
        $version = request()->route('version');
        if ($version === '1.0') {
            if ($response instanceof ThemeUpdateCheckResponse) {
                $response = $response->toArray();
            }
            return response(serialize((object) $response), $statusCode);
        }
        return response()->json($response, $statusCode);
    }
}
