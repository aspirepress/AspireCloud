<?php

namespace App\Jobs;

use App\Enums\AssetType;
use App\Models\WpOrg\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadAsset implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly AssetType $type,
        public readonly string $slug,
        public readonly string $file,
        public readonly string $upstreamUrl,
        public readonly ?string $revision = null,
    ) {}

    public function handle(): void
    {
        logger()->info("Downloading asset: {$this->slug}/{$this->file}");
        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
        ])->get($this->upstreamUrl);

        if (!$response->successful()) {
            logger()->error("Failed to download asset: {$this->slug}/{$this->file}");
            return;
        }

        // Generate a storage path
        $localPath = $this->generateLocalPath();

        logger()->info("Storing asset: {$this->slug}/{$this->file} at {$localPath}");

        // Store the file
        Storage::put($localPath, $response->body());

        $version = $this->extractVersion();

        // Create or update record
        $asset = Asset::create([
            'asset_type' => $this->type->value,
            'slug' => $this->slug,
            'version' => $this->extractVersion(),
            'revision' => $this->revision,
            'upstream_path' => $this->upstreamUrl,
            'local_path' => $localPath,
        ]);

        logger()->info('Asser Created ' . $asset->id);
    }

    private function generateLocalPath(): string
    {
        $basePath = match ($this->type) {
            AssetType::CORE_ZIP => 'core',
            AssetType::PLUGIN_ZIP => "plugins/{$this->slug}",
            AssetType::THEME_ZIP => "themes/{$this->slug}",
            AssetType::SCREENSHOT,
            AssetType::BANNER => "assets/{$this->slug}",
        };

        return "{$basePath}/{$this->file}";
    }

    private function extractVersion(): ?string
    {
        return match ($this->type) {
            AssetType::CORE_ZIP => $this->extractCoreVersion(),
            AssetType::PLUGIN_ZIP,
            AssetType::THEME_ZIP => $this->extractPackageVersion(),
            default => null,
        };
    }

    private function extractCoreVersion(): ?string
    {
        if (\Safe\preg_match('/wordpress-([\d.]+)/', $this->file, $matches)) {
            return rtrim($matches[1], '.');
        }

        return null;
    }

    private function extractPackageVersion(): ?string
    {
        if (\Safe\preg_match('/\d+\.\d+(?:\.\d+)?/', $this->file, $matches)) {
            return $matches[0];
        }

        return null;
    }
}
