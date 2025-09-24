<?php

namespace App\Http\Controllers\API\Elastic;

use App\Http\Controllers\Controller;
use Elastic\Elasticsearch\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ElasticSearchController extends Controller
{
    /**
     * Search plugins
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
        // query
        if ($query === '') {
            $body = [
                'query' => [
                    'match_all' => (object)[],
                ],
            ];
        } else {
            $body = [
                'query' => [
                    'bool' => [
                        'should' => [
                            ['wildcard' => ['name' => '*' . strtolower($query) . '*']],
                            ['wildcard' => ['slug' => '*' . strtolower($query) . '*']],
                            ['wildcard' => ['description' => '*' . strtolower($query) . '*']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
            ];
        }
        // search
        $response = $client
            ->search([
                'index' => 'plugins',
                'body' => $body,
                'from' => $offset,
                'size' => $limit,
            ]);

        $hits = $response['hits']['hits'] ?? [];
        $total = $response['hits']['total']['value'] ?? 0;
        $results = array_map(fn($hit) => $hit['_source'], $hits);
        // response
        return response()
            ->json([
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
                'results' => $results,
            ]);
    }
}
