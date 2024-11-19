<?php

namespace App\Models\WpOrg;

use App\Models\BaseModel;
use Carbon\CarbonImmutable;
use Database\Factories\WpOrg\ClosedPluginFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * @property-read string $id
 * @property-read string $slug
 * @property-read string $name
 * @property-read string $description
 * @property-read CarbonImmutable $closed_date
 * @property-read string $reason
 * @property-read array|null $metadata
 * @property-read CarbonImmutable $ac_created
 * @property-read string $ac_shadow_id          if previously open plugin exists, this is its id.  usually null.
 */
class ClosedPlugin extends BaseModel
{
    use HasUuids;

    /** @use HasFactory<ClosedPluginFactory> */
    use HasFactory;

    protected $table = 'closed_plugins';

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'slug' => 'string',
            'name' => 'string',
            'description' => 'string',
            'closed_date' => 'immutable_datetime',
            'reason' => 'string',
            'metadata' => 'array',
            'ac_created' => 'immutable_datetime',
            'ac_shadow_id' => 'string',
        ];
    }

    /** @param array<string, mixed> $metadata */
    public static function fromSyncMetadata(array $metadata): self
    {
        $syncmeta = $metadata['aspiresync_meta'];
        $syncmeta['type'] === 'plugin' or throw new InvalidArgumentException("invalid type '{$syncmeta['type']}'");
        $syncmeta['status'] === 'closed' or throw new InvalidArgumentException("invalid status '{$syncmeta['status']}'");

        $trunc = fn(?string $str, int $len = 255) => ($str === null) ? null : Str::substr($str, 0, $len);

        return self::create([
            'slug' => $metadata['slug'],
            'name' => $trunc($metadata['name']),
            'description' => $metadata['description'],
            'closed_date' => CarbonImmutable::parse($metadata['closed_date']),
            'reason' => $metadata['reason'],
            'metadata' => $metadata,
            'ac_created' => now(),
        ]);
    }
}