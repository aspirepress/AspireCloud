<?php

namespace App\Values\WpOrg\Plugins;

use App\Models\WpOrg\PluginTag;
use Bag\Attributes\Transforms;
use Bag\Bag;

readonly class PluginHotTagsResponse extends Bag
{
    public function __construct(
        public string $slug,
        public string $name,
        public int $count,
    ) {}

    /** @return array{slug: string, name: string, count: int} */
    #[Transforms(PluginTag::class)]
    public static function fromPluginTag(PluginTag $tag): array
    {
        return ['slug' => $tag->slug, 'name' => $tag->name, 'count' => $tag->plugins_count];
    }
}
