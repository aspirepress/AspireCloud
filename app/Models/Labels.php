<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Labels extends Model
{
    use HasUuids;

    protected $fillable = [
        'package_release_id',
        'type',
        'value',
        'data',
        'source',
        'class',
        'updated_at',
    ];

    protected $casts = [
        'id' => 'string',
        'package_release_id' => 'string',
        'type' => 'string',
        'value' => 'string',
        'data' => 'array',
        'source' => 'string',
        'class' => 'string',
        'created_at' => 'immutable_datetime',
        'updated_at' => 'datetime',
    ];

    // Vulnerability value constants
    public const VALUE_HIGH = 'high';
    public const VALUE_MEDIUM = 'medium';
    public const VALUE_LOW = 'low';

    /** @return BelongsTo<PackageRelease, $this> */
    public function packageRelease(): BelongsTo
    {
        return $this->belongsTo(PackageRelease::class, 'package_release_id', 'id');
    }

    /**
     * Parse vulnerability value from API value
     */
    public static function parsevalue(string $value): string
    {
        // API returns "vulnerable:high", "vulnerable:medium", "vulnerable:low"
        if (str_contains($value, ':')) {
            $parts = explode(':', $value);
            $value = strtolower($parts[1] ?? 'none');

            return match ($value) {
                'high' => self::VALUE_HIGH,
                'medium' => self::VALUE_MEDIUM,
                'low' => self::VALUE_LOW,
                default => '',
            };
        }

        return self::VALUE_NONE;
    }

    /**
     * Get the highest severity value for a package release
     */
    public static function getHighestSeverity(string $packageReleaseId): ?string
    {
        $label = self::where('package_release_id', $packageReleaseId)
                     ->orderByRaw("
                CASE value
                    WHEN 'high' THEN 1
                    WHEN 'medium' THEN 2
                    WHEN 'low' THEN 3
                    ELSE 4
                END
            ")
                     ->first();

        return $label?->value;
    }
}
