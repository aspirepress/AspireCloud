<?php

namespace App\Services;

use App\Models\WpOrg\Plugin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PluginInformationService
{
    public function findBySlug(string $slug): ?Plugin
    {
        return Plugin::query()->where('slug', $slug)->first();
    }

    /**
     * Query plugins with filters and pagination
     *
     * @return array{
     *    plugins: Collection<int, Plugin>,
     *    page: int,
     *    totalPages: int,
     *    total: int
     * }
     */
    public function queryPlugins(
        int $page,
        int $perPage,
        ?string $search = null,
        ?string $tag = null,
        ?string $author = null,
        string $browse = 'popular',
    ): array {
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

        $this->applyBrowseSort($query, $browse);

        $total = $query->count();
        $totalPages = (int) ceil($total / $perPage);

        $plugins = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'plugins' => $plugins,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
        ];
    }

    /**
     * Apply sorting based on browse parameter
     *
     * @param Builder<Plugin> $query
     */
    private function applyBrowseSort(Builder $query, string $browse): void
    {
        match ($browse) {
            'new' => $query->orderBy('added', 'desc'),
            'updated' => $query->orderBy('last_updated', 'desc'),
            'top-rated' => $query->orderBy('rating', 'desc'),
            default => $query->orderBy('active_installs', 'desc'),
        };
    }
}
