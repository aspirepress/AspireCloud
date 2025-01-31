<?php

namespace App\Services\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Http\Resources\ThemeCollection;
use App\Http\Resources\ThemeResource;
use App\Models\WpOrg\Theme;
use Illuminate\Database\Eloquent\Builder;

class QueryThemesService
{
    public function queryThemes(QueryThemesRequest $req): ThemeCollection
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $skip = ($page - 1) * $perPage;

        $themesBaseQuery = Theme::query()
            ->orderBy('last_updated', 'desc')   // default sort
            ->when($req->browse, self::applyBrowse(...))
            ->when($req->search, self::applySearch(...))
            ->when($req->theme, self::applyTheme(...))
            ->when($req->author, self::applyAuthor(...))
            ->when($req->tags, self::applyTags(...));

        $total = $themesBaseQuery->count();

        $themes = $themesBaseQuery
            ->skip($skip)
            ->take($perPage)
            ->with('author')
            ->get();

        $collection = collect($themes)
            ->map(fn($theme) => (new ThemeResource($theme))->additional(['fields' => $req->fields]));

        return new ThemeCollection($collection, $page, (int) ceil($total / $perPage), $total);
    }

    /** @param Builder<Theme> $query */
    private static function applyBrowse(Builder $query, string $browse): void
    {
        // TODO: replicate 'featured' browse (currently it's identical to 'popular')
        match ($browse) {
            'popular', 'featured' => $query->reorder('rating', 'desc'),
            'new' => $query->reorder('creation_time', 'desc'),
            default => null,
        };
    }

    /** @param Builder<Theme> $query */
    private static function applySearch(Builder $query, string $search): void
    {
        $query
            ->whereFullText('slug', $search)
            ->orWhereFullText('name', $search)
            ->orWhereFullText('description', $search);
    }

    /** @param Builder<Theme> $query */
    private static function applyTheme(Builder $query, string $theme): void
    {
        $query->whereFullText('slug', $theme);
    }

    /** @param Builder<Theme> $query */
    private static function applyAuthor(Builder $query, string $author): void
    {
        $query->whereHas('author', fn(Builder $q) => $q->where('user_nicename', 'like', "%$author%"));
    }

    /**
     * @param Builder<Theme> $query
     * @param string[] $tags
     */
    private static function applyTags(Builder $query, array $tags): void
    {
        $query->whereHas('tags', fn(Builder $q) => $q->whereIn('slug', $tags));
    }
}
