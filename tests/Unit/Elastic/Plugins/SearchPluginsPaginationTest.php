<?php
declare(strict_types=1);

use App\Actions\Elastic\SearchPlugins;
use Elastic\Elasticsearch\Client;

it('handles pagination', function () {
    $mockClient = createMockClient(
        [
            'hits' => [
                'total' => ['value' => 100],
                'hits' => [],
            ],
        ],
        function ($params) {
            expect($params['from'])->toBe(20)
                ->and($params['size'])->toBe(10);
        }
    );

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => 'test',
        'limit' => 10,
        'offset' => 20,
    ]);

    $result = $action();

    expect($result['total'])->toBe(100)
        ->and($result['limit'])->toBe(10)
        ->and($result['offset'])->toBe(20);
});
