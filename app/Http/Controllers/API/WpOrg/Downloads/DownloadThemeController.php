<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

class DownloadThemeController
{
    public function __invoke(string $file): mixed
    {
        return 'DownloadTheme ' . $file;
    }
}
