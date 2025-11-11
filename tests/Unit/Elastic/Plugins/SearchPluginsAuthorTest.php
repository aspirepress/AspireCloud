<?php
declare(strict_types=1);

use App\Actions\Elastic\SearchPlugins;
use Elastic\Elasticsearch\Client;

it('filters by author', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [
                    [
                        '_source' => [
                            'name' => 'Author Plugin',
                            'author' => 'johndoe',
                        ],
                    ],
                ],
            ],
        ],
        function ($params) {
            expect($params['body']['query']['bool'])->toHaveKey('should')
                ->and($params['body']['query']['bool'])->toHaveKey('minimum_should_match');
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => 'plugin',
        'limit' => 10,
        'offset' => 0,
        'author' => 'johndoe',
    ]);

    $result = $action();

    expect($result['total'])->toBe(1)
        ->and($result['results'][0]['author'])->toBe('johndoe');
});
