<?php

namespace App\Models;

use App\Models\WpOrg\Author;
use App\Values\Packages\PackageData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Package extends BaseModel
{
    use HasUuids;

    protected $table = 'packages';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'did' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'description' => 'string',
            'download_url' => 'string',
            'version' => 'string',
            'origin_id' => 'string',
            'package_type_id' => 'string',
            'raw_metadata' => 'array',
        ];
    }

    /** @return BelongsToMany<Author, covariant self> */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'package_authors', 'package_id', 'author_id', 'id', 'id');
    }

    public static function fromPackageData(PackageData $packageData): self
    {
        $origin = Origin::where('slug', $packageData->origin)->first();
        if (!$origin) {
            throw new \InvalidArgumentException("Origin not found: {$packageData->origin}");
        }

        $type = $origin->packageTypes()->where('slug', $packageData->type)->first();
        if (!$type) {
            throw new \InvalidArgumentException("Package type not found: {$packageData->type}");
        }

        $package = self::_create([
            'did' => $packageData->did,
            'name' => $packageData->name,
            'slug' => $packageData->slug,
            'description' => $packageData->description,
            'download_url' => $packageData->download_url,
            'version' => $packageData->version,
            'origin_id' => $origin->id,
            'package_type_id' => $type->id,
            'raw_metadata' => $packageData->raw_metadata,
        ]);

        // Authors
        foreach ($packageData->authors as $authorData) {
            $author = Author::where([
                'user_nicename' => $authorData['name'],
                'author_url' => $authorData['url'] ?? null,
            ])->first();

            if (!$author) {
                $author = Author::create([
                    'user_nicename' => $authorData['name'],
                    'profile' => $authorData['url'] ?? null,
                    //'avatar' => $authorData['email'] ?? null,
                    'display_name' => $authorData['name'],
                    'author' => $authorData['name'],
                    'author_url' => $authorData['url'] ?? null,
                ]);
            }
            $package->authors()->attach($author->id);
        }

        return $package;
    }
}
