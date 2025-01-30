<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\Plugin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class QueryPluginsService
{
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
        ?string $tag = null, // TODO: make this work with more than one tag, the way Themes do
        ?string $author = null,
        string $browse = 'popular',
    ): array {
        $query = Plugin::query()
            ->when($browse, self::applyBrowse(...))
            ->when($search, self::applySearch(...))
            ->when($tag, self::applyTag(...))
            ->when($author, self::applyAuthor(...));

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

    /** @param Builder<Plugin> $query */
    private static function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $q) use ($search) {
            $q
                ->whereFullText('slug', $search)
                ->orWhereFullText('name', $search)
                ->orWhereFullText('short_description', $search)
                ->orWhereFullText('description', $search);
        });
    }

    /** @param Builder<Plugin> $query */
    private static function applyAuthor(Builder $query, string $author): void
    {
        $query->whereLike('author', $author);
    }

    /** @param Builder<Plugin> $query */
    private static function applyTag(Builder $query, string $tag): void
    {
        $query->whereHas('tags', fn(Builder $q) => $q->whereIn('slug', [$tag]));
    }

    /**
     * Apply sorting based on browse parameter
     *
     * @param Builder<Plugin> $query
     */
    private static function applyBrowse(Builder $query, string $browse): void
    {
        // TODO: replicate 'featured' browse (currently it's identical to 'popular')
        match ($browse) {
            'new' => $query->reorder('added', 'desc'),
            'updated' => $query->reorder('last_updated', 'desc'),
            'top-rated', 'popular', 'featured' => $query->reorder('rating', 'desc'),
            default => $query->reorder('active_installs', 'desc'),
        };
    }
}
