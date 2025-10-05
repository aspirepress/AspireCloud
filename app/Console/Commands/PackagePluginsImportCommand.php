<?php

namespace App\Console\Commands;

use App\Enums\PackageType;
use App\Models\Package;
use App\Models\WpOrg\Plugin;
use App\Values\Packages\PackageData;
use Exception;
use Illuminate\Support\Facades\DB;

use function Safe\ini_set;

class PackagePluginsImportCommand extends Command
{
    protected $signature = 'package:plugins-import {--new-only} {--stop-on-first-error}';

    protected $description = 'Import existing plugins as packages';

    private int $currentItem = 0;

    private int $errors = 0;

    private int $loaded = 0;

    private int $chunkSize = 100;

    public function handle(): void
    {
        ini_set('memory_limit', '-1');

        Plugin::query()
            ->lazy($this->chunkSize)
            ->each(function ($plugin) {
                $this->currentItem++;
                $this->info("#$this->currentItem: $plugin->slug");
                try {
                    DB::beginTransaction();
                    // Plugins don't have a DID, so we use the slug to find existing packages.
                    // @todo - review this logic.
                    $package = Package::query()
                        ->where([
                            ['slug', '=', $plugin->slug],
                            ['type', '=', PackageType::PLUGIN->value],
                        ])
                        ->first();
                    if ($package && $this->option('new-only')) {
                        DB::rollBack();
                        return;
                    }
                    $package?->delete();

                    Package::fromPackageData(PackageData::from($plugin));
                    $this->loaded++;
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $this->errors++;
                    $this->error("Item $this->currentItem: {$e->getMessage()}");
                    $this->option('stop-on-first-error') and $this->fail('Errors encountered -- aborting.');
                }
            });

        if ($this->errors > 0) {
            $this->fail("Imported $this->loaded items; $this->errors errors");
        }

        $this->info("Imported $this->loaded items.");
    }
}
