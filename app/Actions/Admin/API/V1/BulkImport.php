<?php

declare(strict_types=1);

namespace App\Actions\Admin\API\V1;

use App\Http\JsonLines;
use App\Http\JsonResponses;
use App\Models\WpOrg\ClosedPlugin;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function Safe\ini_set;

class BulkImport
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

        Log::info("Beginning bulk import", $request_info);

        foreach ($this->lazyJsonLines($request) as $metadata) {
            $currentLine++;
            try {
                $model = DB::transaction(fn() => $this->loadOne($metadata));
                assert($model instanceof Model); // strip 'mixed' type from DB::transaction
                Log::debug(
                    "Imported {$model->slug}",
                    ['slug' => $model->slug, 'version' => $model->version, 'type' => $model->getMorphClass()],
                );
                $imported++;
            } catch (Exception $e) {
                $errors[$currentLine] = $e->getMessage();
            }
        }

        Log::info("Bulk import complete", [...$request_info, 'imported' => $imported, 'errors' => $errors]);

        return $errors ? $this->error(compact('errors', 'imported')) : $this->success(['imported' => $imported]);
    }

    /** @param array<string, mixed> $metadata */
    private function loadOne(array $metadata): Plugin|ClosedPlugin|Theme
    {
        $sync_meta = $metadata['aspiresync_meta'];
        $type = $sync_meta['type'];
        $status = $sync_meta['status'];

        $class = match ($type) {
            'plugin' => match ($status) {
                'open' => Plugin::class,
                'closed' => ClosedPlugin::class,
                default => throw new Exception("Unknown plugin status: {$status}"),
            },
            'theme' => match ($status) {
                'open' => Theme::class,
                // Closed themes don't seem to be a thing, they're just 404 in the API
                default => throw new Exception("Unknown theme status: {$status}"),
            },
            default => throw new Exception("Unknown plugin type: {$type}"),
        };

        $slug = $metadata['slug'];

        assert(is_a($class, Model::class, true));

        $class::query()->where('slug', $slug)->delete();
        return $class::fromSyncMetadata($metadata);
    }
}
