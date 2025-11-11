<?php
declare(strict_types=1);

use App\Actions\Elastic\SearchPlugins;
use Elastic\Elasticsearch\Client;

it('searches plugins with query', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 2],
                'hits' => [
                    [
                        '_source' => [
                            'name' => 'Test Plugin',
                            'slug' => 'test-plugin',
                            'description' => 'A test plugin',
                        ],
                    ],
                    [
                        '_source' => [
                            'name' => 'Another Plugin',
                            'slug' => 'another-plugin',
                            'description' => 'Another test plugin',
                        ],
                    ],
                ],
            ],
        ],
        function ($params) {
            expect($params['index'])->toBe('plugins')
                ->and($params['from'])->toBe(0)
                ->and($params['size'])->toBe(10);
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => 'test',
        'limit' => 10,
        'offset' => 0,
    ]);

    $result = $action();

    expect($result['total'])->toBe(2)
        ->and($result['limit'])->toBe(10)
        ->and($result['offset'])->toBe(0)
        ->and($result['results'])->toHaveCount(2)
        ->and($result['results'][0]['name'])->toBe('Test Plugin');
});

it('uses match_all when search query is empty', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 0],
                'hits' => [],
            ],
        ],
        function ($params) {
            expect($params['body']['query']['bool']['must'][0])->toHaveKey('match_all');
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => '',
        'limit' => 10,
        'offset' => 0,
    ]);

    $result = $action();

    expect($result['total'])->toBe(0);
});
