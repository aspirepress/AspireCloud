<?php

namespace App\Services\Packages;

use App\Models\Package;

class PackageDIDService
{
    /**
     * @param string $type
     * @param string $slug
     * @return string
     */
    public function generateWebDid(string $type, string $slug): string
    {
        $parsedUrl = \Safe\parse_url(config('app.url'));
        $domain = $parsedUrl['host'];
        if (!$domain) {
            throw new \RuntimeException('Invalid APP_URL configuration');
        }

        return sprintf('did:web:%s:%s:%s', $domain, $type, $slug);
    }

    /**
     * @param Package $package
     * @return string
     */
    public function generatePackageWebDid(Package $package): string
    {
        return $this->generateWebDid($package->type, $package->slug);
    }
}
