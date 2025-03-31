<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

use function Safe\json_encode;

class PassThroughController extends Controller
{
    public static bool $expectsHit = false; // hack for tests

    public function __invoke(Request $request): Response
    {
        if (app()->environment('testing') && !self::$expectsHit) {
            throw new \RuntimeException('Unexpected request to pass-through controller in testing environment');
        }

        $requestData = $request->all();
        $ua = $request->header('User-Agent');
        $path = $request->path();
        $queryParams = $request->query();

        $response = Http::withHeaders(['User-Agent' => $ua, 'Accept' => '*/*'])
            ->asForm()
            ->send(
                $request->getMethod(),
                "https://api.wordpress.org/$path",
                ['query' => $queryParams, 'form_params' => $requestData],
            );

        $content = $response->body();
        $this->logRequestAndResponse($request, $response, $content);
        return response($content, $response->status(), ['Content-Type', $response->header('Content-Type')]);
    }

    private function logRequestAndResponse(Request $request, ClientResponse $response, string $content): void
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
