<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Export;

use App\Http\Controllers\Controller;
use App\Services\Exports\ExportService;
use App\Values\WpOrg\Export\ExportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {}

    public function __invoke(Request $request, string $type): StreamedResponse
    {
        $req = ExportRequest::from([
            ...$request->all(), // @mago-expect lint:no-request-all
            'type' => $type,
        ]);

        $path = $this->exportService->getExportedFilePath($req);
        return $this->streamFromS3($path);
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
