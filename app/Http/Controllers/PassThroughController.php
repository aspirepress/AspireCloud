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

        $path = $request->path();
        $queryParams = $request->query();

        $headers = $this->filterRequestHeaders($request);

        $response = Http::withHeaders($headers)
            ->withBody($request->getContent())
            ->send(
                $request->getMethod(),
                "https://api.wordpress.org/$path/", // always add the trailing /
                ['query' => $queryParams],
            );

        $content = $response->body();
        $this->logRequestAndResponse($request, $response, $content);
        return response($content, $response->status(), $response->headers());
    }

    /** @return array<string, string> */
    private function filterRequestHeaders(Request $request): array
    {
        $headers = $request->headers->all();

        $filter = fn($value, $key) => match (true) {
            $key === 'host', $key === 'content-length' => false,
            str_starts_with($key, 'x-') => false,
            default => true,
        };

        $mapWpHeader = fn(string $key) => str_starts_with($key, 'wp-') ? str_replace('-', '_', $key) : $key;

        /** @param list<string> $value */
        $mapHeaders = fn(array $value, string $key) => [$mapWpHeader($key) => $value[0]];

        return collect($headers)->filter($filter)->mapWithKeys($mapHeaders)->toArray();
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
