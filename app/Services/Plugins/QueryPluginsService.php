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
    /**
     * Query plugins with filters and pagination
     */
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

        // Build the base query with filters
        $query = Plugin::query()
            ->when($tag, self::applyTag(...))
            ->when($browse, self::applyBrowse(...)) // applies default sort order
            ->when($author, self::applyAuthor(...)); // todo order by similarity to normalized search term

        // If search is provided, use the weighted search which returns a new query
        if ($search) {
            $query = self::applySearchWeighted($query, $search, $req);
        }

        $total = $query->count();
        $totalPages = (int)ceil($total / $perPage);

        $plugins = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->unique('slug')
            ->map(fn($plugin) => PluginResponse::from($plugin)->asQueryPluginsResponse());

        return QueryPluginsResponse::from([
            'plugins' => $plugins,
            'info' => ['page' => $page, 'pages' => $totalPages, 'results' => $total],
        ]);
    }

    /** @param Builder<Plugin> $query */
    public static function applySearch(Builder $query, string $search, QueryPluginsRequest $request): void
    {
        $lcsearch = mb_strtolower($search);
        $slug = Regex::replace('/[^-\w]+/', '-', $lcsearch);

        // Normalize trigram search string
        $wordchars = Regex::replace('/\W+/', '', $lcsearch);

        $query->where('slug', $slug); // need an initial condition or it retrieves everything

        $q = Plugin::query();

        // Sadly I can't make %> work this way.  But it's already sanitized so interpolating it is safe.
        // $slug_similar = $q->clone()->where('slug', '%>', $search);

        $slug_prefix = $q->clone()->whereLike('slug', "$slug%");
        $slug_similar = $q->clone()->whereRaw("slug %> '$wordchars'");

        $name_exact = $q->clone()->where('name', $search);
        $name_prefix = $q->clone()->whereLike('name', "$search%");
        $name_similar = $q->clone()->whereRaw("name %> '$wordchars'");

        $short_description_similar = $q->clone()->whereRaw("short_description %> '$wordchars'");
        $description_fulltext = $q->clone()->whereFullText('description', $search);

        $query->unionAll($name_exact);
        $query->unionAll($slug_prefix);
        $query->unionAll($name_prefix);
        $query->unionAll($slug_similar);
        $query->unionAll($name_similar);
        $query->unionAll($short_description_similar);
        $query->unionAll($description_fulltext);

        // ❌ Unfortunately this doesn't work since the similarity has to be in each union clause
        // $column = self::browseToSortColumn($request->browse);
        // $query->selectRaw("pow(3 * similarity(name, '$wordchars'), 2) * log($column) as score");
        // $query->reorder('score', 'desc');
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

        // Normalize trigram search string
        $wordchars = Regex::replace('/\W+/', '', $lcsearch);

        // Get the sort column based on browse parameter
        $sortColumn = self::browseToSortColumn($request->browse);

        // Create a base query for the CTE (Common Table Expression)
        $baseQuery = null;

        // Create individual queries with their respective score calculations

        // Name exact match (highest priority)
        $nameExact = Plugin::query()
            ->where('name', $search)
            ->selectRaw("*, pow(10, 2) * log($sortColumn) as score");

        // Slug prefix match (high priority)
        $slugPrefix = Plugin::query()
            ->whereLike('slug', "$slug%")
            ->selectRaw("*, pow(5, 2) * log($sortColumn) as score");

        // Name prefix match (high priority)
        $namePrefix = Plugin::query()
            ->whereLike('name', "$search%")
            ->selectRaw("*, pow(5, 2) * log($sortColumn) as score");

        // Slug similarity match
        $slugSimilar = Plugin::query()
            ->whereRaw("slug %> '$wordchars'")
            ->selectRaw("*, pow(3 * similarity(slug, '$wordchars'), 2) * log($sortColumn) as score");

        // Name similarity match
        $nameSimilar = Plugin::query()
            ->whereRaw("name %> '$wordchars'")
            ->selectRaw("*, pow(3 * similarity(name, '$wordchars'), 2) * log($sortColumn) as score");

        // Short description similarity match
        $shortDescSimilar = Plugin::query()
            ->whereRaw("short_description %> '$wordchars'")
            ->selectRaw("*, pow(2 * similarity(short_description, '$wordchars'), 2) * log($sortColumn) as score");

        // Description full-text match
        $descFulltext = Plugin::query()
            ->whereFullText('description', $search)
            ->selectRaw("*, pow(1.5, 2) * log($sortColumn) as score");

        // Combine all queries with unionAll
        $baseQuery = $nameExact;
        $baseQuery->unionAll($slugPrefix);
        $baseQuery->unionAll($namePrefix);
        $baseQuery->unionAll($slugSimilar);
        $baseQuery->unionAll($nameSimilar);
        $baseQuery->unionAll($shortDescSimilar);
        $baseQuery->unionAll($descFulltext);

        // Create a new query that wraps the unionAll query and orders by score
        $wrappedQuery = Plugin::query()
            ->fromSub($baseQuery, 'weighted_plugins')
            ->orderBy('score', 'desc');

        return $wrappedQuery;
    }

    /** @param Builder<Plugin> $query */
    public static function applyAuthor(Builder $query, string $author): void
    {
        $query->whereRaw("author %> '$author'");
    }

    /** @param Builder<Plugin> $query */
    public static function applyTag(Builder $query, string $tag): void
    {
        $query->whereHas('tags', fn(Builder $q) => $q->whereIn('slug', [$tag]));
    }

    /**
     * Apply sorting based on browse parameter
     *
     * @param Builder<Plugin> $query
     */
    public static function applyBrowse(Builder $query, string $browse): void
    {
        $query->reorder(self::browseToSortColumn($browse), 'desc');
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
