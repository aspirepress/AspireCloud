<?php

namespace App\Models\Traits;

use Elastic\Elasticsearch\Client;

trait Indexable
{
    public static function bootIndexable(): void
    {
        static::created(fn ($model) => $model->addToIndex());
        static::updated(fn ($model) => $model->addToIndex());
        static::deleted(fn ($model) => $model->removeFromIndex());
    }

    public function addToIndex(): void
    {
        $client = app(Client::class);

        $client->index([
            'index' => $this->getSearchIndex(),
            'id'    => $this->getKey(),
            'body'  => $this->toSearchArray(),
        ]);
    }

    public function removeFromIndex(): void
    {
        $client = app(Client::class);

        $client->delete([
            'index' => $this->getSearchIndex(),
            'id'    => $this->getKey(),
        ]);
    }

    abstract public function getSearchIndex(): string;
    abstract public function toSearchArray(): array;
}
