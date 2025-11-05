<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageRelease extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'package_id',
        'version',
        'download_url',
        'requires',
        'suggests',
        'provides',
        'artifacts',
        'signature',
        'checksum',
    ];

    protected $casts = [
        'id' => 'string',
        'package_id' => 'string',
        'version' => 'string',
        'download_url' => 'string',
        'requires' => 'array',
        'suggests' => 'array',
        'provides' => 'array',
        'artifacts' => 'array',
        'signature' => 'string',
        'checksum' => 'string',
        'created_at' => 'immutable_datetime',
    ];

    /** @return BelongsTo<Package, $this> */
    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    /** @return HasMany<Labels, $this> */
    public function labels(): HasMany
    {
        return $this->hasMany(Labels::class, 'package_release_id', 'id');
    }

    /**
     * Get the highest severity vulnerability for this release
     */
    public function getHighestVulnerabilitySeverity(): ?string
    {
        return Labels::getHighestSeverity($this->id);
    }

    /**
     * Check if this release has any vulnerabilities
     */
    public function hasVulnerabilities(): bool
    {
        return $this->labels()->exists();
    }

    /**
     * Scope to get releases with specific vulnerability severity
     */
    public function scopeWithVulnerabilitySeverity($query, string $severity)
    {
        return $query->whereHas('labels', function ($q) use ($severity) {
            $q->where('status', $severity);
        });
    }

    /**
     * Scope to get releases that need CVE checking based on last update time
     */
    public function scopeNeedsCveCheck($query, int $hours = 24)
    {
        return $query->whereDoesntHave('labels')
                     ->orWhereHas('labels', function ($q) use ($hours) {
                         $q->where('updated_at', '<', now()->subHours($hours));
                     });
    }
}
