<?php
declare(strict_types=1);

use App\Actions\Elastic\SearchPlugins;
use Elastic\Elasticsearch\Client;

it('handles elasticsearch exceptions', function () {
    $mockClient = new class {
        public function search(array $params) {
            throw new Exception('Connection failed');
        }
    };

    app()->instance(Client::class, $mockClient);

    $action = new SearchPlugins([
        'search' => 'test',
        'limit' => 10,
        'offset' => 0,
    ]);

    $result = $action();

    expect($result['total'])->toBe(0)
        ->and($result)->toHaveKey('error')
        ->and($result['error'])->toBe('Connection failed');
});



