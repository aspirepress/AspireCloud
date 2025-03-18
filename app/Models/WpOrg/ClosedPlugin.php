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
 * @property-read array<string, mixed>|null $metadata
 * @property-read CarbonImmutable $ac_created
 * @property-read string $ac_shadow_id          if previously open plugin exists, this is its id.  usually null.
 * @property-read string $ac_origin
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
            'ac_origin' => 'string',
            'ac_created' => 'immutable_datetime',
            'ac_shadow_id' => 'string',
            'ac_raw_metadata' => 'array',
        ];
    }

    public function getReasonText(): string
    {
        return match ($this->reason) {
            'author-request' => 'Author Request',
            'guideline-violation' => 'Guideline Violation',
            'licensing-trademark-violation' => 'Licensing/Trademark Violation',
            'merged-into-core' => 'Merged into Core',   // lowercase 'i' is in upstream response
            'security-issue' => 'Security Issue',
            'unused' => 'Unused',  // here for completeness with upstream code, but it actually _is_ unused
            default => 'Other/Unknown Reason', // AspireCloud addition
        };
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
            'ac_raw_metadata' => $metadata,
            'ac_origin' => $syncmeta['origin'],
            'ac_created' => now(),
        ]);
    }
}
