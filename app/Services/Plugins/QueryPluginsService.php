<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\Plugin;
use App\Utils\Regex;
use App\Values\WpOrg\Plugins\PluginResponse;
use App\Values\WpOrg\Plugins\QueryPluginsRequest;
use App\Values\WpOrg\Plugins\QueryPluginsResponse;
use Illuminate\Database\Eloquent\Builder;
use function Laravel\Prompts\error;

class QueryPluginsService
{
    public function queryPlugins(QueryPluginsRequest $req): QueryPluginsResponse
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $browse = $req->browse ?: 'popular';
        $search = $req->search;
        $tags = $req->tags ?? [];
        $author = $req->author;

        $search = self::normalizeSearchString($search);
        $tags = array_map(fn($tag) => self::normalizeSearchString($tag), (array) $tags);
        $author = self::normalizeSearchString($author);

        // Ad hoc pipeline because Laravel's Pipeline class is awful
        $callbacks = collect();
        $search and $callbacks->push(fn($query) => self::applySearchWeighted($query, $search, $req));
        $tags and $callbacks->push(fn($query) => self::applyTag($query, $tags));
        $author and $callbacks->push(fn($query) => self::applyAuthor($query, $author));
        !$search and $callbacks->push(fn($query) => self::applyBrowse($query, $browse)); // search applies its own sort

        $query = $callbacks->reduce(fn($query, $callback) => $callback($query), Plugin::query());
        assert($query instanceof Builder);

        $total = $query->count();
        $totalPages = (int)ceil($total / $perPage);

        $plugins = $query
            ->with('contributors')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->unique('slug')
            ->map(fn($plugin) => PluginResponse::from($plugin));

        return QueryPluginsResponse::from([
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
    public static function applySearchWeighted(Builder $query, string $search, QueryPluginsRequest $request): Builder
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
    public static function applyTag(Builder $query, array $tags): Builder
    {
        return $query->whereHas('tags', fn(Builder $q) => $q->whereIn('slug', $tags));
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

    public static function normalizeSearchString(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }
        $search = trim($search);
        $search = Regex::replace('/\s+/i', ' ', $search);
        return Regex::replace('/[^\w.,!?@#$_-]/i', ' ', $search); // strip most punctuation, allow a small subset
    }
}
