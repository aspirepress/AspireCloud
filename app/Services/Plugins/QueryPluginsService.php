<?php

namespace App\Services\Plugins;

use App\Models\WpOrg\Plugin;
use App\Utils\Regex;
use App\Values\WpOrg\Plugins\PluginResponse;
use App\Values\WpOrg\Plugins\QueryPluginsRequest;
use App\Values\WpOrg\Plugins\QueryPluginsResponse;
use Illuminate\Database\Eloquent\Builder;

class QueryPluginsService
{
    public function queryPlugins(QueryPluginsRequest $req): QueryPluginsResponse
    {
        $page = $req->page;
        $perPage = $req->per_page;
        $browse = $req->browse ?: 'popular';
        $search = $req->search;
        $tag = $req->tags[0] ?? null;   // TODO: multiple tags support
        $author = $req->author;

        $search = self::normalizeSearchString($search);
        $tag = self::normalizeSearchString($tag);
        $author = self::normalizeSearchString($author);

        // Ad hoc pipeline because Laravel's Pipeline class is awful
        $callbacks = collect();
        $search and $callbacks->push(fn($query) => self::applySearchWeighted($query, $search, $req));
        $tag and $callbacks->push(fn($query) => self::applyTag($query, $tag));
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

        $weightSimilar = fn($column) => "pow(5 * similarity($column, '$wordchars'), 2) * log(greatest($sortColumn, 1))";

        $slugExact = Plugin::query()
            ->where('slug', $search)
            ->selectRaw("*, 999999999999 as score");

        $nameExact = Plugin::query()
            ->where('name', $search)
            ->selectRaw("*, 999999999999 as score");

        $slugPrefix = Plugin::query()
            ->whereLike('slug', "$slug%")
            ->selectRaw("*, 2 * log(greatest($sortColumn, 1)) as score");

        $namePrefix = Plugin::query()
            ->whereLike('name', "$search%")
            ->selectRaw("*, 2 * log(greatest($sortColumn, 1)) as score");

        $slugSimilar = Plugin::query()
            ->whereRaw("slug %> '$wordchars'")
            ->selectRaw("*, " . $weightSimilar('slug') . " as score");

        $nameSimilar = Plugin::query()
            ->whereRaw("name %> '$wordchars'")
            ->selectRaw("*, " . $weightSimilar('name') . " as score");

        $shortDescSimilar = Plugin::query()
            ->whereRaw("short_description %> '$wordchars'")
            ->selectRaw("*, " . $weightSimilar('short_description') . " as score");

        $descFulltext = Plugin::query()
            ->whereFullText('description', $search)
            ->selectRaw("*, 3 * log(greatest($sortColumn, 1)) as score");

        $baseQuery = $slugExact;
        $baseQuery->unionAll($nameExact);
        $baseQuery->unionAll($slugPrefix);
        $baseQuery->unionAll($namePrefix);
        $baseQuery->unionAll($slugSimilar);
        $baseQuery->unionAll($nameSimilar);
        $baseQuery->unionAll($shortDescSimilar);
        $baseQuery->unionAll($descFulltext);

        return Plugin::query()
            ->fromSub($baseQuery, 'weighted_plugins')
            ->orderBy('score', 'desc');
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
    public static function applyTag(Builder $query, string $tag): Builder
    {
        return $query->whereHas('tags', fn(Builder $q) => $q->whereIn('slug', [$tag]));
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
