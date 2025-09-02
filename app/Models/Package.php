<?php

namespace App\Models;

use App\Models\WpOrg\Author;
use App\Values\Packages\PackageData;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

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
            'raw_metadata' => 'array',
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

    /** @return MorphMany<Tag> */
    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    /** @return HasOne<PackageMetas> */
    public function metas(): HasOne
    {
        return $this->hasOne(PackageMetas::class, 'package_id', 'id');
    }

    /**
     * @param PackageData $packageData
     * @return self
     */
    public static function fromPackageData(PackageData $packageData): self
    {
        return DB::transaction(function () use ($packageData) {
            // Origin & Type
            [$origin, $type] = self::resolveOriginAndType($packageData->origin, $packageData->type);
            // Upsert package
            $package = self::upsertPackage($packageData, $origin->id, $type->id);
            // tags
            self::syncTags($package, $packageData->raw_metadata['keywords'] ?? []);
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
            self::syncAuthors($package, $packageData->authors ?? []);

            return $package;
        });
    }

    /**
     * Resolves the origin and package type by their slugs.
     *
     * @param string $originSlug
     * @param string $typeSlug
     * @return array{Origin, PackageType}
     *
     * @throws \InvalidArgumentException if the origin or type is not found.
     */
    protected static function resolveOriginAndType(string $originSlug, string $typeSlug): array
    {
        $origin = Origin::where('slug', $originSlug)->first();
        if (!$origin) {
            throw new \InvalidArgumentException("Origin not found: {$originSlug}");
        }

        $type = $origin->packageTypes()->where('slug', $typeSlug)->first();
        if (!$type) {
            throw new \InvalidArgumentException("Package type not found: {$typeSlug}");
        }

        return [$origin, $type];
    }

    /**
     * @param PackageData $packageData
     * @param string $originId
     * @param string $typeId
     * @return self
     */
    protected static function upsertPackage(PackageData $packageData, string $originId, string $typeId): self
    {
        return self::_updateOrCreate(
            $packageData->did
                ? ['did' => $packageData->did]
                : ['origin_id' => $originId, 'slug' => $packageData->slug],
            [
                'did' => $packageData->did ?? '',
                'slug' => $packageData->slug,
                'name' => $packageData->name,
                'description' => $packageData->description,
                'origin_id' => $originId,
                'package_type_id' => $typeId,
                'raw_metadata' => $packageData->raw_metadata ?: null,
            ]
        );
    }

    /**
     * @param Package $package
     * @param array $keywords
     * @return void
     */
    protected static function syncTags(self $package, array $keywords): void
    {
        foreach ($keywords as $keyword) {
            if (!is_string($keyword) || $keyword === '') {
                continue;
            }
            $package
                ->tags()
                ->firstOrCreate(
                    ['slug' => Str::slug($keyword)],
                    ['name' => $keyword]
                );
        }
    }

    /**
     * @param Package $package
     * @param array $authors
     * @return void
     */
    protected static function syncAuthors(self $package, array $authors): void
    {
        foreach ($authors as $author) {
            $author = Author::firstOrCreate(
                [
                    'user_nicename' => $author['name'] ?? '',
                    'author_url' => $author['url'] ?? null
                ],
                [
                    'profile' => $author['url'] ?? null,
                    'display_name' => $author['name'] ?? '',
                    'author' => $author['name'] ?? ''
                ]
            );

            $package->authors()->syncWithoutDetaching([$author->id]);
        }
    }
}
