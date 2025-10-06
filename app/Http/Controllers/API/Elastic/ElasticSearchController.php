<?php

namespace App\Http\Controllers\API\Elastic;

use App\Http\Controllers\Controller;
use App\Values\WpOrg\Plugins\ElasticPluginsRequest;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\JsonResponse;

class ElasticSearchController extends Controller
{
    /**
     * Search plugins with text and tag filters
     *
     * @param ElasticPluginsRequest $request
     * @param Client $client
     * @return JsonResponse
     */
    public function search(ElasticPluginsRequest $request, Client $client): JsonResponse
    {
        $query  = strtolower(trim($request->search ?? ''));
        $limit  = $request->limit;
        $offset = $request->offset;
        // accept tags and tag
        $tags    = $request->tags ?? $request->tag ?? [];
        $tagsAnd = $request->tagsAnd ?? [];
        $tagsOr  = $request->tagsOr ?? [];
        $tagsNot = $request->tagsNot ?? [];

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
        // es search
        $response = $client
            ->search([
                'index' => 'plugins',
                'body' => [
                    'query' => $finalQuery,
                ],
                'from' => $offset,
                'size' => $limit,
            ]);

        $hits = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
        $results = array_map(fn($hit) => $hit['_source'], $hits);

        return response()->json([
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'results' => $results,
        ]);
    }
}
