<?php

namespace App\Http\Controllers\API\Elastic;

use Illuminate\Http\Request;
use Elastic\Elasticsearch\Client;
use App\Http\Controllers\Controller;

class ElasticSearchController extends Controller
{
    public function search(Request $request, Client $client)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return response()->json(['error' => 'Missing search query'], 400);
        }

        $response = $client->search([
            'index' => 'plugins',
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            ['wildcard' => ['name'        => '*' . strtolower($query) . '*']],
                            ['wildcard' => ['slug'        => '*' . strtolower($query) . '*']],
                            ['wildcard' => ['description' => '*' . strtolower($query) . '*']],
                        ],
                        'minimum_should_match' => 1,
                    ],
                ],
            ],
        ]);

        return response()->json($response->asArray());
    }
}
