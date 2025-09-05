<?php

namespace App\Services\PluginServices;

use App\Models\WpOrg\Plugin;
use App\Utils\Regex;
use App\Values\WpOrg\Plugins;
use Illuminate\Database\Eloquent\Builder;

class QueryPluginsService
{
    public function queryPlugins(Plugins\QueryPluginsRequest $req): Plugins\QueryPluginsResponse
    {
        $page    = $req->page;
        $perPage = $req->per_page;
        $browse  = $req->browse ?: 'popular';
        $search  = $req->search ?? null;
        $author  = $req->author ?? null;

        // Operators coming from the DTO
        $tags   = $req->tags   ?? [];
        $tagAnd = $req->tagAnd ?? [];
        $tagOr  = $req->tagOr  ?? [];
        $tagNot = $req->tagNot ?? [];

        // merge base tags with tagOr
        $anyTags = array_values(array_unique([...$tags, ...$tagOr]));

        // Ad hoc pipeline because Laravel's Pipeline class is awful
        $callbacks = collect();

        !empty($anyTags) && $callbacks->push(fn($q) => self::applyTagAny($q, $anyTags));
        !empty($tagAnd)  && $callbacks->push(fn($q) => self::applyTagAll($q, $tagAnd));
        !empty($tagNot)  && $callbacks->push(fn($q) => self::applyTagNot($q, $tagNot));

        $search && $callbacks->push(fn($q) => self::applySearchWeighted($q, $search, $req));
        $author && $callbacks->push(fn($q) => self::applyAuthor($q, $author));
        !$search && $callbacks->push(fn($q) => self::applyBrowse($q, $browse));
        /** @var Builder<Plugin> $query */
        $query = $callbacks->reduce(fn($query, $callback) => $callback($query), Plugin::query());

        $total = $query->count();
        $totalPages = (int)ceil($total / $perPage);

        $plugins = $query
            ->with('contributors')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->unique('slug')
            ->map(fn($plugin) => Plugins\PluginResponse::from($plugin));

        return Plugins\QueryPluginsResponse::from([
            'plugins' => $plugins,
            'info' => ['page' => $page, 'pages' => $totalPages, 'results' => $total],
        ]);
    }

    /**
     * Apply weighted search with proper scoring for each union clause
     *
     * @param Builder<Plugin> $query
     * @return Builder<Plugin> Returns a new query with weighted search applied
     */
    public static function applySearchWeighted(
        Builder $query,
        string $search,
        Plugins\QueryPluginsRequest $request
    ): Builder
    {
        $lcsearch = mb_strtolower($search);
        $slug = Regex::replace('/[^-\w]+/', '-', $lcsearch);
        $wordchars = Regex::replace('/\W+/', '', $lcsearch);
        $sortColumn = self::browseToSortColumn($request->browse);

        return $query
            ->where(fn($q) => $q
                ->where('slug', $search)
                ->orWhere('name', 'like', "$search%")
                ->orWhereRaw("slug %> ?", [$wordchars])
                ->orWhereRaw("name %> ?", [$wordchars])
                ->orWhereRaw("short_description %> ?", [$wordchars])
                ->orWhereFullText('description', $search)
            )
            ->selectRaw("plugins.*,
            CASE
                WHEN slug = ? THEN 1000000
                WHEN name = ? THEN 900000
                WHEN slug LIKE ? THEN 800000
                WHEN name LIKE ? THEN 700000
                WHEN slug %> ? THEN 600000
                WHEN name %> ? THEN 500000
                WHEN short_description %> ? THEN 400000
                WHEN to_tsvector('english', description) @@ plainto_tsquery(?) THEN 300000
                ELSE 0
            END + log(GREATEST($sortColumn, 1)) AS score", [
                $search,
                $search,
                "$slug%",
                "$search%",
                $wordchars,
                $wordchars,
                $wordchars,
                $search,
            ])
            ->orderByDesc('score');
    }

    /** @param Builder<Plugin> $query */
    public static function applyAuthor(Builder $query, string $author): Builder
    {
        return $query->where(fn(Builder $q)
            => $q
            ->whereRaw("author %> '$author'")
            ->orWhereHas(
                'contributors',
                fn(Builder $q)
                    => $q
                    ->whereRaw("user_nicename %> '$author'")
                    ->orWhereRaw("display_name %> '$author'"),
            ));
    }

    /** @param Builder<Plugin> $query */
    public static function applyTagAny(Builder $query, array $tags): Builder
    {
        return $query->whereHas('tags', fn(Builder $q) => $q->whereIn('slug', $tags));
    }

    /** @param Builder<Plugin> $query */
    public static function applyTagAll(Builder $query, array $tags): Builder
    {
        return $query->whereHas(
            'tags',
            fn(Builder $q) => $q->whereIn('slug', $tags),
            '>=',
            count($tags)
        );
    }

    /** @param Builder<Plugin> $query */
    public static function applyTagNot(Builder $query, array $slugs): Builder
    {
        return $query->whereDoesntHave('tags', fn(Builder $q) => $q->whereIn('slug', $slugs));
    }

    /**
     * Apply sorting based on browse parameter
     *
     * @param Builder<Plugin> $query
     */
    public static function applyBrowse(Builder $query, string $browse): Builder
    {
        return $query->reorder(self::browseToSortColumn($browse), 'desc');
    }

    public static function browseToSortColumn(?string $browse): string
    {
        return match ($browse) {
            'new' => 'added',
            'updated' => 'last_updated',
            'top-rated', 'featured' => 'rating',
            default => 'active_installs',
        };
    }
}
