<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use function Safe\json_encode;

class CatchAllController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $requestData = $request->all();
        $ua = $request->header('User-Agent');
        $path = $request->path();
        $queryParams = $request->query();

        // If path is root, return a 200 OK empty response
        if ($path === '/') {
            return response()->noContent(200);
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => $ua,
                'Accept' => '*/*',
            ])->asForm()->send($request->getMethod(), 'https://api.wordpress.org/' . $path, [
                'query' => $queryParams,
                'form_params' => $requestData,
            ]);

        } catch (RequestException $e) {
            $statusCode = $e->response->status();

            return response()->noContent($statusCode);
        }

        // Get content type and status code
        $contentType = $response->header('Content-Type');
        $statusCode = $response->status();
        $content = $response->body();

        // Log request and response in DB
        $this->saveData($request, $response, $content);

        // Forward response through
        return response($content, $statusCode)->header('Content-Type', $contentType);
    }

    private function saveData(Request $request, ClientResponse $response, string $content): void
    {
        DB::table('request_data')->insert([
            'id' => Str::uuid()->toString(),
            'request_path' => $request->path(),
            'request_query_params' => json_encode($request->query()),
            'request_body' => json_encode($request->all()),
            'request_headers' => json_encode($request->headers->all()),
            'response_code' => $response->status(),
            'response_body' => $content,
            'response_headers' => json_encode($response->headers()),
            'created_at' => Carbon::now(),
        ]);
    }
}
