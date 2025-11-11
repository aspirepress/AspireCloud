<?php
declare(strict_types=1);

namespace App\Services\Elastic;

use App\Models\WpOrg\Plugin;
use Elastic\Elasticsearch\Client;

readonly class IndexService
{

    public function __construct(private Client $client) {}

    public function add(Plugin $plugin): void
    {
        /** @noinspection PhpParamsInspection (phpstorm is confused by Client::index phpdoc) */
        $this->client->index([
            'index' => 'plugins',
            'id' => (string)$plugin->getKey(),
            'body' => $this->toDocumentArray($plugin),
        ]);
    }

    public function remove(Plugin $plugin): void
    {
        $this->client->delete([
            'index' => 'plugins',
            'id' => (string)$plugin->getKey(),
        ]);
    }

    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     slug: string,
     *     description: string,
     *     short_description: string,
     *     author: string,
     *     contributors: string[],
     *     tags: string[],
     *     rating: int,
     *     active_installs: int,
     *     last_updated: string|null,
     *     added: string|null
     * }
     */
    public function toDocumentArray(Plugin $plugin): array
    {
        return [
            'id' => $plugin->id,
            'name' => $plugin->name,
            'slug' => $plugin->slug,
            'description' => $plugin->description,
            'short_description' => $plugin->short_description,
            'author' => $plugin->author,
            'contributors' => $plugin->contributors->pluck('display_name')->map(fn($n) => strtolower($n))->all() ?? [],
            'tags' => $plugin->tags->pluck('name')->map(fn($t) => strtolower($t))->all() ?? [],
            'rating' => $plugin->rating,
            'active_installs' => $plugin->active_installs,
            'last_updated' => optional($plugin->last_updated)?->toDateString(),
            'added' => optional($plugin->added)?->toDateString(),
        ];
    }
}
