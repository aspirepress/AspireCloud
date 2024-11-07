<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

class DownloadPluginController
{
    public function __invoke(string $slug): mixed
    {
        return 'Download Plugin: ' . $slug;
    }
}
