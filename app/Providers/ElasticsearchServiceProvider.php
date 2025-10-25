<?php
declare(strict_types=1);

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this
            ->app
            ->singleton(
                Client::class,
                fn () => ClientBuilder::create()
                        ->setHosts([config('elasticsearch.host')])
                        ->build()
            );
    }
    /**
     * @return array<class-string>
     */
    public function provides(): array
    {
        return [Client::class];
    }
}
