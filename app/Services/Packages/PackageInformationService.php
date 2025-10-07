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

    public function findBySlug(string $slug): Package|null
    {
        return Package::query()->where('slug', $slug)->first();
    }
}
