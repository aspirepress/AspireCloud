<?php

namespace App\Http\Controllers\API\WpOrg\Export;

use App\Http\Controllers\Controller;
use App\Services\Exports\ExportService;
use App\Values\WpOrg\Export\ExportRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {}

    public function __invoke(Request $request, string $type): Response
    {
        $req = ExportRequest::from([
            ...$request->all(),
            'type' => $type,
        ]);

        return $this->exportService->export($req);
    }
}
