<?php

namespace App\Console\Commands;

use App\Enums\PackageType;
use App\Models\Package;
use App\Models\WpOrg\Theme;
use App\Values\Packages\PackageData;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Safe\ini_set;

class PackageThemesImportCommand extends Command
{
    protected $signature = 'package:themes-import {--new-only} {--stop-on-first-error}';

    protected $description = 'Import existing themes as packages';

    private int $currentItem = 0;

    private int $errors = 0;

    private int $loaded = 0;

    private int $chunkSize = 100;

    public function handle(): void
    {
        ini_set('memory_limit', '-1');

        Theme::with('author')
            ->lazy($this->chunkSize)
            ->each(function ($theme) {
                $this->currentItem++;
                $this->info("#$this->currentItem: $theme->slug");
                try {
                    DB::beginTransaction();
                    // Themes don't have a DID, so we use the slug to find existing packages.
                    // @todo - review this logic.
                    $package = Package::query()
                        ->where([
                            ['slug', '=', $theme->slug],
                            ['type', '=', PackageType::THEME->value],
                        ])
                        ->first();
                    if ($package && $this->option('new-only')) {
                        return;
                    }
                    $package?->delete();

                    Package::fromPackageData(PackageData::from($theme));
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
