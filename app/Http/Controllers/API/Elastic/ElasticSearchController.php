<?php

namespace App\Http\Controllers\API\Elastic;

use App\Http\Controllers\Controller;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElasticSearchController extends Controller
{
    /**
     * Search plugins with text and tag filters
     *
     * @param Request $request
     * @param Client $client
     * @return JsonResponse
     */
    public function search(Request $request, Client $client): JsonResponse
    {
        $query = trim($request->input('q', ''));
        $limit = (int)$request->input('limit', 1000);
        $offset = (int)$request->input('offset', 0);

        // accept tags and tag
        $tags    = $this->normalizeTags($request->input('tags', $request->input('tag', [])));
        // tag operators
        $tagsAnd = $this->normalizeTags($request->input('tags_and', []));
        $tagsOr  = $this->normalizeTags($request->input('tags_or', []));
        $tagsNot = $this->normalizeTags($request->input('tags_not', []));

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

    /**
     * @param $input
     * @return array
     */
    private function normalizeTags($input): array
    {
        if (is_array($input)) {
            return array_values(array_filter(array_map('trim', $input)));
        }

        if (is_string($input)) {
            return array_values(array_filter(array_map('trim', explode(',', $input))));
        }

        return [];
    }
}
