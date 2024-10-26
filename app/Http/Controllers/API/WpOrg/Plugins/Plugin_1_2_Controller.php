<?php

namespace App\Http\Controllers\API\WpOrg\Plugins;

use App\Http\Controllers\Controller;
use App\Http\Resources\PluginCollection;
use App\Http\Resources\PluginResource;
use App\Models\WpOrg\Plugin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Plugin_1_2_Controller extends Controller
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $action = $request->query('action');
        $slug   = $request->query('slug');

        return match ($action) {
            'query_plugins' => $this->queryPlugins($request),
            'plugin_information' => $this->pluginInformation($slug),
            default => response()->json(['error' => 'Invalid action'], 400)
        };
    }

    private function pluginInformation(?string $slug): JsonResponse
    {
        if (!$slug) {
            return response()->json(['error' => 'Slug is required'], 400);
        }

        $plugin = Plugin::query()->where('slug', $slug)->first();

        if (!$plugin) {
            return response()->json(['error' => 'Plugin not found'], 404);
        }

        return response()->json(new PluginResource($plugin));
    }

    private function queryPlugins(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query('page', '1'));
        $perPage = (int) $request->query('per_page', '24');
        $search = $request->query('search');
        $tag = $request->query('tag');
        $author = $request->query('author');

        // Build query
        $query = Plugin::query()
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($tag, function (Builder $query, string $tag) {
                $query->whereJsonContains('tags', $tag);
            })
            ->when($author, function (Builder $query, string $author) {
                $query->where('author', 'like', "%{$author}%");
            });

        // Get total count for pagination
        $total = $query->count();
        $totalPages = (int) ceil($total / $perPage);

        // Get paginated results
        $plugins = $query
            ->orderBy('last_updated', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json(new PluginCollection(
            PluginResource::collection($plugins),
            $page,
            $totalPages,
            $total
        ));
    }
}
