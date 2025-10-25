<?php
declare(strict_types=1);

use App\Actions\Elastic\SearchPlugins;
use Elastic\Elasticsearch\Client;

it('sorts by browse parameter', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 5],
                'hits' => [],
            ],
        ],
        function ($params) {
            expect($params['body']['sort'])->not->toBeEmpty()
                ->and($params['body']['sort'][0])->toHaveKey('active_installs');
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => '',
        'limit' => 10,
        'offset' => 0,
        'browse' => 'popular',
    ]);

    $result = $action();

    expect($result['total'])->toBe(5);
});

it('sorts by different browse types', function ($browse, $expectedField) {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [],
            ],
        ],
        function ($params) use ($expectedField) {
            expect($params['body']['sort'])->not->toBeEmpty()
                ->and($params['body']['sort'][0])->toHaveKey($expectedField);
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => '',
        'limit' => 10,
        'offset' => 0,
        'browse' => $browse,
    ]);

    $result = $action();

    expect($result['total'])->toBe(1);
})->with([
    ['new', 'added'],
    ['updated', 'last_updated'],
    ['top-rated', 'rating'],
    ['featured', 'rating'],
    ['popular', 'active_installs'],
]);

