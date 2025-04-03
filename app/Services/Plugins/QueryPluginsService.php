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

        $query = Plugin::query()
            ->when($browse, self::applyBrowse(...))
            ->when($search, self::applySearch(...))
            ->when($tag, self::applyTag(...))
            ->when($author, self::applyAuthor(...));

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
    private static function applySearch(Builder $query, string $search): void
    {
        $slug = Regex::replace('/[^a-z0-9-]+/i', '-', $search);
        $query->where('slug', $slug); // need an initial condition or it retrieves everything

        $q = Plugin::query();

        // I can't make %> work this way, only whereRaw works.  TODO: find out why.
        // $slug_similar = $q->clone()->where('slug', '%>', $search);

        $slug_similar = $q->clone()->whereRaw("slug %> '$search'");
        $name_exact = $q->clone()->where('name', $search);
        $name_similar = $q->clone()->whereRaw("name %> '$search'");
        $short_description_similar = $q->clone()->whereRaw("short_description %> '$search'");
        $description_fulltext = $q->clone()->whereFullText('description', $search);

        $query->unionAll($name_exact);
        $query->unionAll($slug_similar);
        $query->unionAll($name_similar);
        $query->unionAll($short_description_similar);
        $query->unionAll($description_fulltext);
    }

    /** @param Builder<Plugin> $query */
    private static function applyAuthor(Builder $query, string $author): void
    {
        $query->whereRaw("author %> '$author'");
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
        // TODO: replicate 'featured' browse (currently it's identical to 'top-rated')
        match ($browse) {
            'new' => $query->reorder('added', 'desc'),
            'updated' => $query->reorder('last_updated', 'desc'),
            'top-rated', 'featured' => $query->reorder('rating', 'desc'),
            default => $query->reorder('active_installs', 'desc'),  // 'popular' is also the default
        };
    }

    private static function normalizeSearchString(?string $search): ?string
    {
        if ($search === null) {
            return null;
        }
        $search = trim($search);
        $search = Regex::replace('/\s+/i', ' ', $search);
        return Regex::replace('/[^\w.,!?@#$_-]/i', ' ', $search); // strip most punctuation, allow a small subset
    }
}
