<?php

namespace App\Http\Controllers\API\WpOrg\Downloads;

class DownloadCoreController
{
    public function __invoke(string $version): mixed
    {
        return 'DownloadCore: ' . $version;
    }
}
