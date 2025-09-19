<?php

namespace App\Models;

use App\Models\WpOrg\Author;
use App\Values\Packages\PackageData;
use Carbon\CarbonImmutable;
use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @property-read string                                                        $id
 * @property-read string                                                        $did
 * @property-read string                                                        $slug
 * @property-read string                                                        $name
 * @property-read string                                                        $description
 * @property-read string                                                        $type
 * @property-read string                                                        $origin
 * @property-read string                                                        $license
 * @property-read array<string, mixed>|null                                     $raw_metadata
 * @property-read CarbonImmutable|null                                          $created_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Author>         $authors
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PackageRelease> $releases
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PackageTag>     $tags
 * @property-read PackageMetas|null                                             $metas
 */
class Package extends BaseModel
{
    use HasUuids;

    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $table = 'packages';

    protected static function booted(): void
    {
        static::deleting(function ($package) {
            // Cascade delete tags.
            $package->tags()->delete();
        });
    }

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
            'created_at' => 'immutable_datetime',
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

    /** @return HasOne<PackageMetas, $this> */
    public function metas(): HasOne
    {
        return $this->hasOne(PackageMetas::class, 'package_id', 'id');
    }

    /** @return BelongsToMany<PackageTag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(PackageTag::class, 'package_package_tag', 'package_id', 'package_tag_id');
    }

    /**
     * @param PackageData $packageData
     * @return self
     */
    public static function fromPackageData(PackageData $packageData): self
    {
        return DB::transaction(function () use ($packageData) {
            $where = $packageData->did
                ? ['did' => $packageData->did]
                : ['origin' => $packageData->origin, 'slug' => $packageData->slug];
            $package = Package::query()->where($where)->first();
            $package?->delete();

            $package = self::create([
                'did' => $packageData->did,
                'slug' => $packageData->slug,
                'name' => $packageData->name,
                'description' => $packageData->description,
                'origin' => $packageData->origin,
                'type' => $packageData->type,
                'license' => $packageData->license,
                'raw_metadata' => $packageData->raw_metadata ?: null,
            ]);

            // tags
            self::syncTags($package, $packageData->tags ?? []);

            // Iterate releases
            foreach ($packageData->releases as $release) {
                // pick primary downloadable artifact
                $artifactsPackage = Arr::get($release, 'artifacts.package', []);
                /** @var array<string, string> $artifactsPackage */
                $artifacts = Arr::first($artifactsPackage) ?? [];

                $package
                    ->releases()
                    ->create([
                        'version' => $release['version'],
                        'download_url' => $artifacts['url'] ?? null,
                        'signature' => $artifacts['signature'] ?? null,
                        'checksum' => $artifacts['checksum'] ?? null,
                        'requires' => $release['requires'] ?? null,
                        'suggests' => $release['suggests'] ?? null,
                        'provides' => $release['provides'] ?? null,
                        'artifacts' => $release['artifacts'] ?? null,
                    ]);
            }

            // Authors
            self::syncAuthors($package, $packageData->authors ?? []);

            // Update security
            $metas = $package->metas['metadata'] ?? [];

            $metas['security'] = $packageData->security;
            $metas['sections'] = $packageData->sections ?? [];

            $package
                ->metas()
                ->create(
                    ['metadata' => $metas],
                );

            return $package;
        });
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
