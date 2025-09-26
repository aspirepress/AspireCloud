<?php

namespace App\Services\Packages;

use App\Models\Package;

class PackageInformationService
{
    /**
     * @param string $did
     * @return Package|null
     */
    public function findByDID(string $did): Package|null
    {
        return Package::query()->where('did', $did)->first();
    }

    /**
     * @param string $type
     * @param string $slug
     * @return Package|null
     */
    public function find(string $type, string $slug): Package|null
    {
        return Package::query()
            ->where('type', $type)
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param string $did
     * @return string
     */
    public function getPackageMetadataUrl(string $did): string
    {
        return route('package.fairMetadata', ['did' => $did], true);
    }

    /**
     * @param Package $package
     * @return string
     */
    public function generatePackageWebDid(Package $package): string
    {
        $parsedUrl = \Safe\parse_url(config('app.url'));
        $domain = $parsedUrl['host'];
        if (!$domain) {
            throw new \RuntimeException('Invalid APP_URL configuration');
        }

        return sprintf('did:web:%s:%s:%s', $domain, $package->type, $package->slug);
    }

    public function findBySlug(string $slug): Package|null
    {
        return Package::query()->where('slug', $slug)->first();
    }
}
