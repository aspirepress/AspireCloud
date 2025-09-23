<?php

namespace App\Providers;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Support\ServiceProvider;

class ElasticsearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            return ClientBuilder::create()
                ->setHosts([config('elasticsearch.host', 'http://localhost:9200')])
                ->build();
        });
    }
}
