<?php

namespace App\Models;

use App\Models\WpOrg\Author;
use App\Values\Packages\PackageData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

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
            'origin_id' => 'string',
            'package_type_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Origin, $this> */
    public function origin(): BelongsTo
    {
        return $this->belongsTo(Origin::class);
    }

    /** @return BelongsTo<PackageType, $this> */
    public function packageType(): BelongsTo
    {
        return $this->belongsTo(PackageType::class);
    }

    /** @return BelongsToMany<Author, $this> */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_package', 'package_id', 'author_id', 'id', 'id');
    }

    /** @return HasMany<PackageRelease> */
    public function releases(): HasMany
    {
        return $this->hasMany(PackageRelease::class, 'package_id', 'id');
    }

    /**
     * @param PackageData $packageData
     * @return self
     */
    public static function fromPackageData(PackageData $packageData): self
    {
        return DB::transaction(function () use ($packageData) {
            // Origin & Type
            $origin = Origin::where('slug', $packageData->origin)->first();
            if (!$origin) {
                throw new \InvalidArgumentException("Origin not found: {$packageData->origin}");
            }

            $type = $origin->packageTypes()->where('slug', $packageData->type)->first();
            if (!$type) {
                throw new \InvalidArgumentException("Package type not found: {$packageData->type}");
            }
            // Create or update package
            $package = self::_updateOrCreate(
                $packageData->did
                    ? ['did' => $packageData->did]
                    : ['origin_id' => $origin->id, 'slug' => $packageData->slug],
                [
                    'did' => $packageData->did ?? '',
                    'slug' => $packageData->slug,
                    'name' => $packageData->name,
                    'description' => $packageData->description,
                    'origin_id' => $origin->id,
                    'package_type_id' => $type->id,
                    'raw_metadata' => $packageData->raw_metadata ?? null,
                ]
            );
            // Iterate releases
            $releases = $packageData->raw_metadata['releases'] ?? null;

            if (is_array($releases) && !empty($releases)) {
                foreach ($releases as $release) {
                    // pick primary downloadable artifact
                    $artifacts = Arr::first(Arr::get($release, 'artifacts.package', [])) ?? [];

                    $package
                        ->releases()
                        ->updateOrCreate(
                            ['version' => $release['version']],
                            [
                                'download_url' => $artifacts['url'] ?? null,
                                'signature' => $artifacts['signature'] ?? null,
                                'checksum' => $artifacts['checksum'] ?? null,

                                'requires' => $release['requires'] ?? null,
                                'suggests' => $release['suggests'] ?? null,
                                'provides' => $release['provides'] ?? null,
                                'artifacts' => $release['artifacts'] ?? null,
                            ]
                        );
                }
            } else {
                // If no releases, create a default one from PackageData
                $package
                    ->releases()
                    ->updateOrCreate(
                        ['version' => $packageData->version],
                        [
                            'download_url' => $packageData->download_url,
                            'requires' => null,
                            'suggests' => null,
                            'provides' => null,
                            'artifacts' => null,
                        ]
                    );
            }

            // Authors
            foreach ($packageData->authors as $authorData) {
                $author = Author::firstOrCreate(
                    [
                        'user_nicename' => $authorData['name'],
                        'author_url' => $authorData['url'] ?? null,
                    ],
                    [
                        'profile' => $authorData['url'] ?? null,
                        'display_name' => $authorData['name'],
                        'author' => $authorData['name'],
                    ]
                );

                $package->authors()->syncWithoutDetaching([$author->id]);
            }

            return $package;
        });
    }
}
