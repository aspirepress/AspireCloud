<?php

namespace App\Actions\Elastic;

use Elastic\Elasticsearch\Client;

/**
 * @phpstan-type SearchPluginsRequest array{
 *     search: string,
 *     limit: int,
 *     offset: int,
 *     tags?: list<string>,
 *     tagsAnd?: list<string>,
 *     tagsOr?: list<string>,
 *     tagsNot?: list<string>,
 *     author?: string|null,
 *     browse?: string|null
 * }
 *
 * @phpstan-type SearchPluginsResult array{
 *     total: int,
 *     limit: int,
 *     offset: int,
 *     results: list<array<string, mixed>>,
 *     error?: string
 * }
 */
readonly class SearchPlugins
{
    /**
     * @param SearchPluginsRequest $request
     */
    public function __construct(private array  $request)
    {
    }
    /**
     * @return SearchPluginsResult
     */
    public function __invoke(): array
    {
        $query = $this->request['search'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        // accept tags and tag
        $tags = $this->request['tags'] ?? [];
        $tagsAnd = $this->request['tagsAnd'] ?? [];
        $tagsOr = $this->request['tagsOr'] ?? [];
        $tagsNot = $this->request['tagsNot'] ?? [];
        // author
        $author = $this->request['author'] ?? null;
        // browse
        $browse = $this->request['browse'] ?? null;
        // query
        if ($query === '') {
            $mustQuery = ['match_all' => (object)[]];
        } else {
            $mustQuery = [
                'bool' => [
                    'should' => [
                        ['wildcard' => ['name' => '*' . strtolower($query) . '*']],
                        ['wildcard' => ['slug' => '*' . strtolower($query) . '*']],
                        ['wildcard' => ['description' => '*' . strtolower($query) . '*']],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];
        }
        // Base query
        $finalQuery = [
            'bool' => [
                'must' => [$mustQuery],
            ],
        ];
        // tags
        if ($tags) {
            $finalQuery['bool']['must'][] = ['terms' => ['tags' => $tags]];
        }
        // tag operators
        if ($tagsAnd) {
            $finalQuery['bool']['must'][] = ['terms' => ['tags' => $tagsAnd]];
        }
        if ($tagsOr) {
            $finalQuery['bool']['should'][] = ['terms' => ['tags' => $tagsOr]];
            $finalQuery['bool']['minimum_should_match'] = 1;
        }
        if ($tagsNot) {
            $finalQuery['bool']['must_not'][] = ['terms' => ['tags' => $tagsNot]];
        }
        // author
        if ($author) {
            $finalQuery['bool']['should'][] = ['match' => ['author' => $author]];
            $finalQuery['bool']['should'][] = ['match' => ['contributors' => $author]];
            $finalQuery['bool']['minimum_should_match'] = 1;
        }
        // sort
        $sort = [];
        if ($browse) {
            $sortField = match ($browse) {
                'new' => 'added',
                'updated' => 'last_updated',
                'top-rated', 'featured' => 'rating',
                default => 'active_installs',
            };
            $sort = [[$sortField => ['order' => 'desc']]];
        }
        // es search
        try {
            $client = app(Client::class);
            $response = $client
                ->search([
                    'index' => 'plugins',
                    'body' => [
                        'query' => $finalQuery,
                        'sort' => $sort,
                    ],
                    'from' => $offset,
                    'size' => $limit,
                ])
                ->asArray();
        } catch (\Throwable $e) {
            return [
                'total' => 0,
                'limit' => $limit,
                'offset' => $offset,
                'results' => [],
                'error' => $e->getMessage(),
            ];
        }

        $hits = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
        $results = array_map(fn($hit) => $hit['_source'], $hits);

        return [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'results' => $results,
        ];
    }
}
