<?php

declare(strict_types=1);

namespace App\Contracts\Downloads;

use App\Enums\AssetType;
use Illuminate\Http\Response;

interface Downloader
{
    public function download(AssetType $type, string $slug, string $file, ?string $revision = null): Response;
}
