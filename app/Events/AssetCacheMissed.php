<?php

namespace App\Events;

use App\Enums\AssetType;
use Illuminate\Foundation\Events\Dispatchable;

readonly class AssetCacheMissed
{
    use Dispatchable;

    public function __construct(
        public AssetType $type,
        public string $slug,
        public string $file,
        public string $upstreamUrl,
        public ?string $revision = null,
    ) {}
}
