<?php

namespace App\Models;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\WpOrg\Author;
use Illuminate\Support\Facades\DB;
use App\Values\Packages\PackageData;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
            'type' => 'string',
            'origin' => 'string',
            'license' => 'string',
            'raw_metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<Author, $this> */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'author_package', 'package_id', 'author_id', 'id', 'id');
    }

    /** @return HasMany<PackageRelease, $this> */
    public function releases(): HasMany
    {
        return $this->hasMany(PackageRelease::class, 'package_id', 'id');
    }

    /** @return MorphMany<Tag, $this> */
    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'taggable');
    }

    /** @return HasOne<PackageMetas, $this> */
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
            // Upsert package
            $package = self::upsertPackage($packageData);
            // tags
            self::syncTags($package, $packageData->raw_metadata['keywords'] ?? []);
            // Iterate releases
            foreach ($packageData->releases as $release) {
                // pick primary downloadable artifact
                $artifactsPackage = Arr::get($release, 'artifacts.package', []);
                /** @var array<string, string> $artifactsPackage */
                $artifacts = Arr::first($artifactsPackage) ?? [];

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
                        ],
                    );
            }

            // Authors
            self::syncAuthors($package, $packageData->authors ?? []);

            // Update security
            $metas = $package->metas['raw_metadata'] ?? [];

            $metas['security'] = $packageData->security;
            $package->metas()->updateOrCreate(
                ['package_id' => $package->id],
                ['raw_metadata' => $metas]
            );

            return $package;
        });
    }

    /**
     * @param PackageData $packageData
     * @return self
     */
    protected static function upsertPackage(PackageData $packageData): self
    {
        return self::_updateOrCreate(
            $packageData->did
                ? ['did' => $packageData->did]
                : ['origin' => $packageData->origin, 'slug' => $packageData->slug],
            [
                'did' => $packageData->did,
                'slug' => $packageData->slug,
                'name' => $packageData->name,
                'description' => $packageData->description,
                'origin' => $packageData->origin,
                'type' => $packageData->type,
                'license' => $packageData->license,
                'raw_metadata' => $packageData->raw_metadata ?: null,
            ],
        );
    }

    /**
     * @param Package $package
     * @param array<string> $keywords
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
                    ['name' => $keyword],
                );
        }
    }

    /**
     * @param Package $package
     * @param array<array<string, string>> $authors
     * @return void
     */
    protected static function syncAuthors(self $package, array $authors): void
    {
        foreach ($authors as $author) {
            $author = Author::firstOrCreate(
                [
                    'user_nicename' => $author['name'] ?? '',
                    'author_url' => $author['url'] ?? null,
                ],
                [
                    'profile' => $author['url'] ?? null,
                    'display_name' => $author['name'] ?? '',
                    'author' => $author['name'] ?? '',
                ],
            );

            $package->authors()->syncWithoutDetaching([$author->id]);
        }
    }
}
