<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Exports\ExportService;
use App\Values\Export\ExportRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function __construct(
        private readonly ExportService $exportService,
    ) {}

    public function __invoke(Request $request, string $type): Response
    {
        try {
            $req = ExportRequest::fromRequest($request, $type);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid request'], 400);
        }

        return $this->exportService->export($req);
    }
}
