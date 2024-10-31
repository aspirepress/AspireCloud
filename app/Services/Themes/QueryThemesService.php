<?php

namespace App\Services\Themes;

use App\Data\WpOrg\Themes\QueryThemesRequest;
use App\Http\Resources\ThemeCollection;
use App\Http\Resources\ThemeResource;
use App\Models\WpOrg\Theme;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class QueryThemesService {
    public function queryThemes(QueryThemesRequest $req): ThemeCollection
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $skip = ($page - 1) * $perPage;

        $themes = Theme::query()
            ->orderBy('last_updated', 'desc')   // default sort
            ->when($req->browse, function ($query, $browse) {
                // TODO: replicate 'featured' browse (currently it's identical to 'popular')
                match ($browse) {
                    'popular', 'featured' => $query->reorder('rating', 'desc'),
                    'new' => $query->reorder('creation_time', 'desc'),
                    default => null,
                };
            })
            ->when($req->search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%")
                    ->orWhereFullText('description', $search);
            })->when($req->theme, function ($query, $search) {
                $query->where('slug', 'ilike', $search);
            })->when($req->author, function (Builder $query, string $author) {
                $query->whereHas('author', function (Builder $query) use ($author) {
                    $query->where('user_nicename', 'like', "%{$author}%");
                });
            })->when($req->tags, function (Builder $query, array $tags) {
                collect($tags)->each(function ($tag) use ($query) {
                    $query->whereJsonContains('tags', $tag);
                });
            })
            ->skip($skip)
            ->take($perPage)
            ->with('author')
            ->get();
        $total = DB::table('themes')->count();

        $collection = collect($themes)->map(fn($theme,
        ) => (new ThemeResource($theme))->additional(['fields' => $req->fields]));

        return new ThemeCollection($collection, $page, (int) ceil($total / $perPage), $total);
    }
}
