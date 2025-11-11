<?php

declare(strict_types=1);

namespace App\Actions\Admin\API\V1;

use App\Http\JsonLines;
use App\Http\JsonResponses;
use App\Models\Package;
use App\Values\Packages\FairMetadata;
use App\Values\Packages\PackageData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Safe\ini_set;

class BulkPackageImport
{
    use JsonResponses;
    use JsonLines;

    public function __invoke(Request $request, Pipeline $pipeline): JsonResponse
    {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '300');

        $currentLine = 0;
        $imported = 0;
        $errors = [];

        $request_info = ['userid' => auth()->user()->id, 'ip' => $request->ip()];

        Log::info('Beginning package import', $request_info);

        foreach ($this->lazyJsonLines($request) as $metadata) {
            $currentLine++;
            try {
                $package = DB::transaction(fn() => $this->loadOne($metadata));
                Log::debug(
                    "Imported {$package->did}",
                    ['slug' => $package->slug, 'type' => $package->type],
                );
                $imported++;
            } catch (Exception $e) {
                $errors[$currentLine] = $e->getMessage();
            }
        }

        Log::info('Bulk import complete', [...$request_info, 'imported' => $imported, 'errors' => $errors]);

        return $errors ? $this->error(compact('errors', 'imported')) : $this->success(['imported' => $imported]);
    }

    /** @param array<string, mixed> $metadata */
    private function loadOne(array $metadata): Package
    {
        $did = $metadata['id'];

        $package = Package::query()->where('did', $did)->first();
        $package?->delete();

        $fairMetadata = FairMetadata::from($metadata);
        return Package::fromPackageData(PackageData::from($fairMetadata));
    }
}
