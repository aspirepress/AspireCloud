<?php

declare(strict_types=1);

namespace App\Http;

use App\Utils\JSON;
use Illuminate\Http\JsonResponse;

trait JsonResponses
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function jsonResponse(array $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse(JSON::fromAssoc($data), $status, $headers, json: true);
    }

    /**
     * @param string|array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function success(string|array $data = 'success', int $status = 200, array $headers = []): JsonResponse
    {
        if (is_string($data)) {
            $data = ['message' => $data];
        }
        return $this->jsonResponse($data, $status, $headers);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     */
    public function error(string|array $data, int $status = 400, array $headers = []): JsonResponse
    {
        if (is_string($data)) {
            $data = ['error' => $data];
        }
        return $this->jsonResponse($data, $status, $headers);
    }
}
