<?php

namespace App\Events;

use App\Models\WpOrg\Asset;
use Illuminate\Foundation\Events\Dispatchable;

readonly class AssetCreated
{
    use Dispatchable;

    public function __construct(public Asset $asset) {}
}
