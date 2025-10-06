<?php

namespace App\Services\Packages;

use App\Models\Package;

class PackageDIDService
{
    public function generateWebDid(string $type, string $slug): string
    {
        $domain = config('fair.domains.webdid')
            ?? \Safe\parse_url(config('app.url'), PHP_URL_HOST)
            ?? throw new \RuntimeException('Cannot determine WEB DID domain from FAIR_DOMAINS_WEBDID or APP_URL');

        return sprintf('did:web:%s:packages:%s:%s', $domain, $type, $slug);
    }

    public function generatePackageWebDid(Package $package): string
    {
        return $this->generateWebDid($package->type, $package->slug);
    }
}
