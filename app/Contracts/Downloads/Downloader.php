<?php

declare(strict_types=1);

namespace App\Contracts\Downloads;

use App\Enums\AssetType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface Downloader
{
    public function download(
        Request $request,
        AssetType $type,
        string $slug,
        string $file,
        ?string $revision = null,
    ): Response;
}
