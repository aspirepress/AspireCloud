<?php

namespace App\Jobs;

use App\Enums\AssetType;
use App\Models\WpOrg\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadAssetJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly AssetType $type,
        public readonly string $slug,
        public readonly string $file,
        public readonly string $upstreamUrl,
        public readonly ?string $revision = null,
    ) {}

    public function handle(): void
    {
        $revstr = $this->revision ? " rev=$this->revision" : '';
        Log::info("Downloading asset for {$this->slug}$revstr from $this->upstreamUrl");

        $response = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
        ])->get($this->upstreamUrl);

        if (!$response->successful()) {
            $status = $response->getStatusCode();
            $message = $response->getReasonPhrase();
            $url = $this->upstreamUrl;
            Log::error("Failed to download asset for {$this->slug}$revstr: $message", compact('status', 'message', 'url'));
            return;
        }

        $localPath = $this->generateLocalPath();
        Storage::put($localPath, $response->body());

        $asset = Asset::create([
            'asset_type' => $this->type->value,
            'slug' => $this->slug,
            'version' => $this->extractVersion(),
            'revision' => $this->revision,
            'upstream_path' => $this->upstreamUrl,
            'local_path' => $localPath,
        ]);

        Log::info(
            "Created new Asset for {$this->slug}$revstr",
            [
                'asset_id' => $asset->id,
                'local_path' => $localPath,
                'slug' => $this->slug,
                'revision' => $this->revision,
            ],
        );
    }

    public function generateLocalPath(): string
    {
        $basePath = match ($this->type) {
            AssetType::CORE => 'core',
            AssetType::PLUGIN => "plugins/{$this->slug}",
            AssetType::THEME => "themes/{$this->slug}",
            AssetType::PLUGIN_SCREENSHOT,
            AssetType::PLUGIN_BANNER => "assets/plugin/{$this->slug}",
            AssetType::PLUGIN_GP_ICON => "gp-icon/plugin/{$this->slug}",
            AssetType::THEME_SCREENSHOT => "assets/theme/{$this->slug}/{$this->revision}",
        };

        return "{$basePath}/{$this->file}";
    }

    private function extractVersion(): ?string
    {
        return match ($this->type) {
            AssetType::CORE => $this->extractCoreVersion(),
            AssetType::PLUGIN,
            AssetType::THEME => $this->extractPackageVersion(),
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
