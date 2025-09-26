<?php

namespace App\Services\Packages;

use App\Models\Package;

class PackageInformationService
{
    public function findByDID(string $did): Package|null
    {
        return Package::query()->where('did', $did)->first();
    }

    public function findBySlug(string $slug): Package|null
    {
        return Package::query()->where('slug', $slug)->first();
    }
}
