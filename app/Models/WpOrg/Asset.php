<?php

namespace App\Models\WpOrg;

use App\Enums\AssetType;
use App\Events\AssetCreated;
use App\Utils\Regex;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\AssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $asset_type
 * @property-read string $slug
 * @property-read string $version
 * @property-read string $revision
 * @property-read string $upstream_path
 * @property-read string $local_path
 * @property-read string $repository
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
class Asset extends Model
{
    use HasUuids;

    /** @use HasFactory<AssetFactory> */
    use HasFactory;

    /** @var array<string, class-string> */
    protected $dispatchesEvents = [
        'created' => AssetCreated::class,
    ];

    protected $fillable = [
        'asset_type',
        'slug',
        'version',
        'revision',
        'upstream_path',
        'local_path',
        'repository',
    ];

    protected $casts = [
        'asset_type' => AssetType::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'repository' => 'wp_org', // this is the db default as well since 0.9.3, but it's handy to have it here too
    ];

    public function getContentType(): string
    {
        $matches = Regex::match('/^.+\.(\w+)$/', $this->local_path);
        if (!$matches) {
            return 'application/octet-stream';
        }
        // hardly exhaustive, but enough to cover our assets ;)
        return match ($matches[1]) {
            'zip' => 'application/zip',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'application/octet-stream',
        };
    }
}
