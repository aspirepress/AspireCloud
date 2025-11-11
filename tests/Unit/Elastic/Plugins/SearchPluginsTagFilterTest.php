<?php
declare(strict_types=1);

use App\Actions\Elastic\SearchPlugins;
use Elastic\Elasticsearch\Client;

it('filters by tags', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [
                    [
                        '_source' => [
                            'name' => 'Tagged Plugin',
                            'tags' => ['security', 'performance'],
                        ],
                    ],
                ],
            ],
        ],
        function ($params) {
            expect($params['body']['query']['bool']['must'][1]['terms'])->toHaveKey('tags');
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => 'plugin',
        'limit' => 10,
        'offset' => 0,
        'tags' => ['security', 'performance'],
    ]);

    $result = $action();

    expect($result['total'])->toBe(1)
        ->and($result['results'][0]['tags'])->toContain('security');
});

it('filters with tag operators', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [],
            ],
        ]
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => 'plugin',
        'limit' => 10,
        'offset' => 0,
        'tagsAnd' => ['security'],
        'tagsOr' => ['performance', 'speed'],
        'tagsNot' => ['deprecated'],
    ]);

    $result = $action();

    expect($result['total'])->toBe(1);
});
