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

use function Safe\ini_set;

class BulkImport
{
    use JsonResponses;
    use JsonLines;

    public function __invoke(Request $request, Pipeline $pipeline): JsonResponse
    {
        ini_set('memory_limit', '2G');

        $currentLine = 0;
        $imported = 0;
        $errors = [];

        foreach ($this->lazyJsonLines($request) as $metadata) {
            $currentLine++;
            try {
                DB::transaction(fn() => $this->loadOne($metadata));
                $imported++;
            } catch (Exception $e) {
                $errors[$currentLine] = $e->getMessage();
            }
        }

        if ($errors) {
            return $this->error(compact('errors'));
        }

        return $this->success(['imported' => $imported]);
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
        return $class::fromSyncMetadata($metadata); // @phpstan-ignore-line
    }
}
