<?php

namespace App\Services\Exports;

use Closure;
use App\Models\WpOrg\Theme;
use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\ClosedPlugin;
use App\Values\WpOrg\Export\ExportRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    protected int $chunkSize = 100;

    /**
     * Export specific packages (plugins, themes, closed plugins) to S3
     * and return a streamed response.
     *
     * @param ExportRequest $req
     * @return StreamedResponse
     */
    public function export(ExportRequest $req): StreamedResponse
    {
        $path = $this->getS3Path($req);
        if (! Storage::disk('s3')->exists($path)) {
            $this->exportToS3($req, $path);
        }

        return $this->streamFromS3($path);
    }

    /**
     * Get the S3 path for the export based on the request parameters.
     *
     * @param ExportRequest $req
     * @return string
     */
    private function getS3Path(ExportRequest $req): string
    {
        $type = $req->type;
        $after = $req->after;

        $basePath = 'exports/' . $type;
        $filename = $type . '-' . ($after ? $after : 'full') . '.ndjson';
        return $basePath . '/' . $filename;
    }

    /**
     * Get the query builder for the specified export type.
     *
     * @param ExportRequest $req
     * @return Builder
     */
    private function getQueryBuilder(ExportRequest $req): Builder
    {
        $type = $req->type;
        $after = $req->after;

        $query = match ($type) {
            'plugins' => Plugin::query(),
            'closed_plugins' => ClosedPlugin::query(),
            'themes' => Theme::query(),
            default => throw new \InvalidArgumentException("Invalid export type: $type"),
        };

        if ($after) {
            $query->where('ac_created', '>=', $after);
        }

        $query->orderBy('ac_created', 'asc');

        return $query;
    }

    /**
     * Get the transformer for the exported model.
     *
     * @return Closure
     */
    private function getTransformer(): Closure
    {
        // All exported models use the ac_raw_metadata field.
        // In the future, if different models need different transformations,
        // specific transformers can be defined here.
        $transformer = function ($record) {
            return $record->ac_raw_metadata;
        };

        return $transformer;
    }

    /**
     * Export the data to S3.
     *
     * @param ExportRequest $req
     * @param string $path
     * @return void
     */
    public function exportToS3(ExportRequest $req, string $path): void
    {
        $queryBuilder = $this->getQueryBuilder($req);
        $transformer = $this->getTransformer();

        $stream = \Safe\fopen('php://temp', 'rw+');

        $queryBuilder->lazy($this->chunkSize)->each(function ($item) use ($stream, $transformer) {
            $line = \Safe\json_encode($transformer($item)) . "\n";
            \Safe\fwrite($stream, $line);
        });

        \Safe\fflush($stream);
        \Safe\rewind($stream);
        Storage::disk('s3')->writeStream($path, $stream);

        \Safe\fclose($stream);
    }

    /**
     * Stream the exported data from S3.
     *
     * @param string $path
     * @return StreamedResponse
     */
    private function streamFromS3(string $path): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($path) {
            $stream = Storage::disk('s3')->readStream($path);
            if (!$stream) {
                throw new \RuntimeException("Failed to read stream from S3 for key: $path");
            }

            while (!feof($stream)) {
                echo fgets($stream, 16384);
            }

            \Safe\fclose($stream);
        });

        $response->headers->set('Content-Type', 'application/x-ndjson');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . basename($path) . '"');

        return $response;
    }
}
